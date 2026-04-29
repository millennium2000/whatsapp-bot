# WhatsApp Scheduled Image Bot

Idozitett WhatsApp bot, amely egy elore megadott idopontban kivalaszt egy veletlen kepet egy sajat/licencelt kep-listabol, majd elkuldi a WhatsApp Cloud API-n keresztul.

Fontos: a bot nem scrape-el oldalakat, es nem adult/erotikus tartalom automatizalasara keszult. WhatsApp Business hasznalatnal tartsd be a Meta/WhatsApp szabalyait, csak opt-in cimzetteknek kuldj uzenetet, es csak olyan kepeket hasznalj, amelyekre van jogod.

## Fajlok

- `bot.py` - az idozito es kuldo logika
- `images.example.json` - pelda kep-lista publikus URL-ekkel
- `.env.example` - szukseges kornyezeti valtozok

## Beallitas

1. Masold le a `.env.example` fajlt `.env` neven.
2. Toltsd ki a WhatsApp Cloud API adataival:
   - `WHATSAPP_TOKEN`
   - `WHATSAPP_PHONE_NUMBER_ID`
   - `RECIPIENT_PHONE`
   - `SEND_TIME`
3. Masold le az `images.example.json` fajlt `images.json` neven, es add meg a sajat/licencelt kepeid URL-jeit.

## Futtatas

A Codex bundled Python runtime-mal pelda:

```powershell
& "C:\Users\toth.csaba2\.cache\codex-runtimes\codex-primary-runtime\dependencies\python\python.exe" bot.py
```

Alapertelmezesben a bot minden nap a `SEND_TIME` idopontban kuld egy kepet.

## Konfiguracio

`.env` pelda:

```dotenv
WHATSAPP_TOKEN=EAAB...
WHATSAPP_PHONE_NUMBER_ID=1234567890
RECIPIENT_PHONE=36123456789
SEND_TIME=09:00
TIMEZONE=Europe/Budapest
IMAGE_LIST_PATH=images.json
CAPTION=Mai kep
GRAPH_API_VERSION=v21.0
```

`RECIPIENT_PHONE` E.164 formatumban legyen, `+` jel nelkul, peldaul magyar szamnal `36...`.
