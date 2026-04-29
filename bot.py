import json
import os
import random
import time
import urllib.error
import urllib.request
from datetime import datetime, timedelta
from pathlib import Path
from zoneinfo import ZoneInfo


DEFAULT_GRAPH_API_VERSION = "v21.0"


def load_env(path=".env"):
    env_path = Path(path)
    if not env_path.exists():
        return

    for raw_line in env_path.read_text(encoding="utf-8").splitlines():
        line = raw_line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        os.environ.setdefault(key.strip(), value.strip().strip('"').strip("'"))


def require_env(name):
    value = os.getenv(name)
    if not value:
        raise RuntimeError(f"Missing required environment variable: {name}")
    return value


def load_images(path):
    image_path = Path(path)
    if not image_path.exists():
        raise RuntimeError(f"Image list not found: {image_path}")

    images = json.loads(image_path.read_text(encoding="utf-8"))
    valid_images = [item for item in images if item.get("url")]
    if not valid_images:
        raise RuntimeError("Image list must contain at least one item with a url.")
    return valid_images


def pick_image(images):
    return random.choice(images)


def next_run_at(send_time, timezone_name):
    hour, minute = [int(part) for part in send_time.split(":", 1)]
    tz = ZoneInfo(timezone_name)
    now = datetime.now(tz)
    target = now.replace(hour=hour, minute=minute, second=0, microsecond=0)
    if target <= now:
        target += timedelta(days=1)
    return target


def send_whatsapp_image(
    graph_api_version, token, phone_number_id, recipient_phone, image_url, caption
):
    url = f"https://graph.facebook.com/{graph_api_version}/{phone_number_id}/messages"
    payload = {
        "messaging_product": "whatsapp",
        "to": recipient_phone,
        "type": "image",
        "image": {
            "link": image_url,
            "caption": caption,
        },
    }

    data = json.dumps(payload).encode("utf-8")
    request = urllib.request.Request(
        url,
        data=data,
        method="POST",
        headers={
            "Authorization": f"Bearer {token}",
            "Content-Type": "application/json",
        },
    )

    try:
        with urllib.request.urlopen(request, timeout=30) as response:
            body = response.read().decode("utf-8")
            return response.status, body
    except urllib.error.HTTPError as error:
        body = error.read().decode("utf-8", errors="replace")
        raise RuntimeError(f"WhatsApp API error {error.code}: {body}") from error


def sleep_until(target):
    while True:
        now = datetime.now(target.tzinfo)
        remaining = (target - now).total_seconds()
        if remaining <= 0:
            return
        time.sleep(min(remaining, 60))


def main():
    load_env()

    token = require_env("WHATSAPP_TOKEN")
    phone_number_id = require_env("WHATSAPP_PHONE_NUMBER_ID")
    recipient_phone = require_env("RECIPIENT_PHONE")
    send_time = os.getenv("SEND_TIME", "09:00")
    timezone_name = os.getenv("TIMEZONE", "Europe/Budapest")
    image_list_path = os.getenv("IMAGE_LIST_PATH", "images.json")
    default_caption = os.getenv("CAPTION", "")
    graph_api_version = os.getenv("GRAPH_API_VERSION", DEFAULT_GRAPH_API_VERSION)

    images = load_images(image_list_path)

    print("WhatsApp scheduled image bot started.")
    print(f"Loaded {len(images)} images from {image_list_path}.")

    while True:
        run_at = next_run_at(send_time, timezone_name)
        print(f"Next send: {run_at.isoformat()}")
        sleep_until(run_at)

        image = pick_image(images)
        caption = image.get("caption") or default_caption
        status, body = send_whatsapp_image(
            graph_api_version=graph_api_version,
            token=token,
            phone_number_id=phone_number_id,
            recipient_phone=recipient_phone,
            image_url=image["url"],
            caption=caption,
        )
        print(f"Sent image. API status: {status}. Response: {body}")
        time.sleep(1)


if __name__ == "__main__":
    main()
