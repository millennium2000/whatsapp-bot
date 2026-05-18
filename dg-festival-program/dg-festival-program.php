<?php
/**
 * Plugin Name: DG Festival Program
 * Description: Festival and family day program schedule shortcode.
 * Version: 0.5.0
 * Author: Duna Group
 * Text Domain: dg-festival-program
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DG_FESTIVAL_PROGRAM_VERSION', '0.5.0');
define('DG_FESTIVAL_PROGRAM_PATH', plugin_dir_path(__FILE__));
define('DG_FESTIVAL_PROGRAM_URL', plugin_dir_url(__FILE__));
define('DG_FESTIVAL_PROGRAM_OPTION', 'dg_festival_program_schedule');
define('DG_FESTIVAL_PROGRAM_VENUES_OPTION', 'dg_festival_program_venues');
define('DG_FESTIVAL_PROGRAM_TAGS_OPTION', 'dg_festival_program_tags');

function dg_festival_program_get_default_venues(): array
{
    return [
        ['id' => 'nagyszinpad', 'label' => 'Nagyszínpad', 'color' => '#c9d7e6', 'text_color' => '#0f2133'],
        ['id' => 'szinhaz', 'label' => 'Színház', 'color' => '#5f96cf', 'text_color' => '#0f2133'],
        ['id' => 'focipalya', 'label' => 'Focipálya', 'color' => '#2b72b8', 'text_color' => '#ffffff'],
        ['id' => 'sportcsarnok', 'label' => 'Sportcsarnok', 'color' => '#f2e799', 'text_color' => '#0f2133'],
        ['id' => 'sportkert', 'label' => 'Sportkert', 'color' => '#ffd200', 'text_color' => '#0f2133'],
        ['id' => 'mozi', 'label' => 'Mozi', 'color' => '#d4aa2c', 'text_color' => '#0f2133'],
    ];
}

function dg_festival_program_get_venues(): array
{
    $venues = get_option(DG_FESTIVAL_PROGRAM_VENUES_OPTION);

    if (!is_array($venues) || empty($venues)) {
        $venues = dg_festival_program_get_default_venues();
    }

    $indexed = [];

    foreach ($venues as $venue) {
        if (!is_array($venue) || empty($venue['id']) || empty($venue['label'])) {
            continue;
        }

        $id = sanitize_key($venue['id']);
        $indexed[$id] = [
            'id' => $id,
            'label' => sanitize_text_field($venue['label']),
            'color' => sanitize_hex_color($venue['color'] ?? '') ?: '#eef3f7',
            'text_color' => sanitize_hex_color($venue['text_color'] ?? '') ?: '#0f2133',
        ];
    }

    if (empty($indexed)) {
        foreach (dg_festival_program_get_default_venues() as $venue) {
            $indexed[$venue['id']] = $venue;
        }
    }

    return $indexed;
}

function dg_festival_program_get_default_program_types(): array
{
    return [
        ['id' => 'main', 'label' => 'Fő program'],
        ['id' => 'registration', 'label' => 'Regisztrációhoz kötött programok'],
        ['id' => 'continuous', 'label' => 'Folyamatos programok'],
    ];
}

function dg_festival_program_get_program_types(): array
{
    $types = get_option(DG_FESTIVAL_PROGRAM_TAGS_OPTION, false);

    if ($types === false) {
        $types = dg_festival_program_get_default_program_types();
    }

    $indexed = [];

    foreach ($types as $type) {
        if (!is_array($type) || empty($type['id']) || empty($type['label'])) {
            continue;
        }

        $id = sanitize_key($type['id']);
        $indexed[$id] = [
            'id' => $id,
            'label' => sanitize_text_field($type['label']),
        ];
    }

    return $indexed;
}

function dg_festival_program_get_default_schedule(): array
{
    return [
        ['time' => '09:00', 'note' => 'Érkezés, regisztráció', 'events' => []],
        ['time' => '09:30', 'note' => 'Kapunyitás', 'events' => []],
        [
            'time' => '10:00',
            'note' => '',
            'events' => [
                ['venue' => 'sportcsarnok', 'title' => 'Focikupa (I. helyszín)', 'type' => 'main'],
                ['venue' => 'sportkert', 'title' => 'Focikupa (II. helyszín)', 'type' => 'main'],
            ],
        ],
        [
            'time' => '10:30',
            'note' => '',
            'events' => [
                ['venue' => 'nagyszinpad', 'title' => 'Megnyitó', 'type' => 'main'],
                ['venue' => 'sportcsarnok', 'title' => 'Focikupa (I. helyszín)', 'type' => 'main'],
                ['venue' => 'sportkert', 'title' => 'Focikupa (II. helyszín)', 'type' => 'main'],
            ],
        ],
        [
            'time' => '11:00',
            'note' => '',
            'events' => [
                ['venue' => 'nagyszinpad', 'title' => 'Kalap Jakab koncert', 'type' => 'main'],
                ['venue' => 'szinhaz', 'title' => 'Ács Fruzsina stand-up műsor', 'type' => 'main'],
                ['venue' => 'sportcsarnok', 'title' => 'Focikupa (I. helyszín)', 'type' => 'main'],
                ['venue' => 'sportkert', 'title' => 'Focikupa (II. helyszín)', 'type' => 'main'],
                ['venue' => 'nagyszinpad', 'title' => 'Haditechnikai Park élményprogram', 'type' => 'registration'],
                ['venue' => 'nagyszinpad', 'title' => 'Balatoni sétahajózás', 'type' => 'registration'],
            ],
        ],
        [
            'time' => '12:00',
            'note' => '',
            'events' => [
                ['venue' => 'nagyszinpad', 'title' => 'Zenés Buborékkaland, Móka Palota', 'type' => 'main'],
                ['venue' => 'sportcsarnok', 'title' => 'KTE Kosárlabda Klub találkozó - Kísérőprogram: Veszprém Cheerleaders Show', 'type' => 'main'],
                ['venue' => 'sportkert', 'title' => 'Focikupa (II. helyszín)', 'type' => 'main'],
            ],
        ],
        [
            'time' => '13:00',
            'note' => '',
            'events' => [
                ['venue' => 'nagyszinpad', 'title' => 'Kicsi Gesztenye Klub koncert', 'type' => 'main'],
                ['venue' => 'szinhaz', 'title' => 'Szabó Győző és Rezes Judit: Párkapcsolatunk színházi előadás', 'type' => 'main'],
                ['venue' => 'sportcsarnok', 'title' => 'Veszprém Handball találkozó - Kísérőprogram: Veszprém Cheerleaders Show', 'type' => 'main'],
                ['venue' => 'nagyszinpad', 'title' => 'Balatoni sétahajózás', 'type' => 'registration'],
            ],
        ],
        [
            'time' => '14:00',
            'note' => '',
            'events' => [
                ['venue' => 'nagyszinpad', 'title' => 'Zenés Buborékkaland, Móka Palota', 'type' => 'main'],
                ['venue' => 'sportcsarnok', 'title' => 'Tiszakécskei LC találkozó - Kísérőprogram: Veszprém Cheerleaders Show', 'type' => 'main'],
                ['venue' => 'focipalya', 'title' => 'DUNA GROUP BIM Show', 'type' => 'main'],
                ['venue' => 'nagyszinpad', 'title' => 'Haditechnikai Park élményprogram', 'type' => 'registration'],
                ['venue' => 'nagyszinpad', 'title' => 'Balatoni sétahajózás', 'type' => 'registration'],
            ],
        ],
        [
            'time' => '15:00',
            'note' => '',
            'events' => [
                ['venue' => 'nagyszinpad', 'title' => 'Zenés Buborékkaland, Móka Palota', 'type' => 'main'],
                ['venue' => 'szinhaz', 'title' => 'Lakatos Péter, Longevity Coach: Életmód mítoszok', 'type' => 'main'],
                ['venue' => 'sportcsarnok', 'title' => 'Aerobic', 'type' => 'main'],
                ['venue' => 'mozi', 'title' => 'Duna Group Kvíz - kvízmesterrel', 'type' => 'main'],
                ['venue' => 'nagyszinpad', 'title' => 'Balatoni sétahajózás', 'type' => 'registration'],
            ],
        ],
        [
            'time' => '16:00',
            'note' => '',
            'events' => [
                ['venue' => 'nagyszinpad', 'title' => 'Don’t Stop The Queen koncert', 'type' => 'main'],
                ['venue' => 'szinhaz', 'title' => 'A malacon nyert királylány / Majorka Színház', 'type' => 'main'],
                ['venue' => 'sportcsarnok', 'title' => 'Jóga', 'type' => 'main'],
                ['venue' => 'nagyszinpad', 'title' => 'Haditechnikai Park élményprogram', 'type' => 'registration'],
            ],
        ],
        ['time' => '16:30', 'note' => '', 'events' => [['venue' => 'nagyszinpad', 'title' => 'EDDA Művek koncert', 'type' => 'main']]],
        ['time' => '17:00', 'note' => '', 'events' => [['venue' => 'sportcsarnok', 'title' => 'Modern Táncház', 'type' => 'main']]],
        ['time' => '18:00', 'note' => 'Rendezvény vége', 'events' => []],
        ['time' => '18:30', 'note' => 'Kapuzárás', 'events' => []],
    ];
}

function dg_festival_program_get_schedule(): array
{
    $schedule = get_option(DG_FESTIVAL_PROGRAM_OPTION);

    if (!is_array($schedule)) {
        return dg_festival_program_get_default_schedule();
    }

    return $schedule;
}

function dg_festival_program_time_to_minutes(string $time): ?int
{
    if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $time, $matches)) {
        return null;
    }

    return ((int) $matches[1] * 60) + (int) $matches[2];
}

function dg_festival_program_sanitize_venues(array $raw_venues): array
{
    $venues = [];

    foreach ($raw_venues as $raw_venue) {
        if (!is_array($raw_venue)) {
            continue;
        }

        $label = isset($raw_venue['label']) ? sanitize_text_field(wp_unslash($raw_venue['label'])) : '';

        if ($label === '') {
            continue;
        }

        $id = isset($raw_venue['id']) ? sanitize_key(wp_unslash($raw_venue['id'])) : '';
        $id = $id !== '' ? $id : sanitize_title($label);
        $id = $id !== '' ? $id : 'venue-' . wp_generate_password(6, false, false);

        $original_id = $id;
        $suffix = 2;

        while (isset($venues[$id])) {
            $id = $original_id . '-' . $suffix;
            $suffix++;
        }

        $venues[$id] = [
            'id' => $id,
            'label' => $label,
            'color' => sanitize_hex_color($raw_venue['color'] ?? '') ?: '#eef3f7',
            'text_color' => sanitize_hex_color($raw_venue['text_color'] ?? '') ?: '#0f2133',
        ];
    }

    return array_values($venues);
}

function dg_festival_program_sanitize_program_types(array $raw_types): array
{
    $types = [];

    foreach ($raw_types as $raw_type) {
        if (!is_array($raw_type)) {
            continue;
        }

        $label = isset($raw_type['label']) ? sanitize_text_field(wp_unslash($raw_type['label'])) : '';

        if ($label === '') {
            continue;
        }

        $id = isset($raw_type['id']) ? sanitize_key(wp_unslash($raw_type['id'])) : '';
        $id = $id !== '' ? $id : sanitize_title($label);
        $id = $id !== '' ? $id : 'tag-' . wp_generate_password(6, false, false);

        $original_id = $id;
        $suffix = 2;

        while (isset($types[$id])) {
            $id = $original_id . '-' . $suffix;
            $suffix++;
        }

        $types[$id] = [
            'id' => $id,
            'label' => $label,
        ];
    }

    return array_values($types);
}

function dg_festival_program_sanitize_schedule(array $raw_schedule, array $venues, array $types): array
{
    $allowed_venues = array_keys($venues);
    $allowed_types = array_keys($types);
    $schedule = [];

    foreach ($raw_schedule as $raw_row) {
        if (!is_array($raw_row)) {
            continue;
        }

        $time = isset($raw_row['time']) ? sanitize_text_field(wp_unslash($raw_row['time'])) : '';
        $note = isset($raw_row['note']) ? sanitize_text_field(wp_unslash($raw_row['note'])) : '';

        if ($time === '' && $note === '' && empty($raw_row['events'])) {
            continue;
        }

        $events = [];

        if (!empty($raw_row['events']) && is_array($raw_row['events'])) {
            foreach ($raw_row['events'] as $raw_event) {
                if (!is_array($raw_event)) {
                    continue;
                }

                $venue = isset($raw_event['venue']) ? sanitize_key(wp_unslash($raw_event['venue'])) : '';
                $title = isset($raw_event['title']) ? sanitize_text_field(wp_unslash($raw_event['title'])) : '';
                $type = isset($raw_event['type']) ? sanitize_key(wp_unslash($raw_event['type'])) : '';
                $end_time = isset($raw_event['end_time']) ? sanitize_text_field(wp_unslash($raw_event['end_time'])) : '';

                if ($title === '' || !in_array($venue, $allowed_venues, true)) {
                    continue;
                }

                if ($type !== '' && !in_array($type, $allowed_types, true)) {
                    $type = '';
                }

                $start_minutes = dg_festival_program_time_to_minutes($time);
                $end_minutes = dg_festival_program_time_to_minutes($end_time);

                if ($start_minutes === null || $end_minutes === null || $end_minutes <= $start_minutes) {
                    $end_time = '';
                }

                $events[] = [
                    'venue' => $venue,
                    'title' => $title,
                    'type' => $type,
                    'end_time' => $end_time,
                ];
            }
        }

        $schedule[] = [
            'time' => $time,
            'note' => $note,
            'events' => $events,
        ];
    }

    usort($schedule, static function (array $a, array $b): int {
        return strcmp($a['time'], $b['time']);
    });

    return $schedule;
}

function dg_festival_program_enqueue_assets(): void
{
    wp_enqueue_style(
        'dg-festival-program',
        DG_FESTIVAL_PROGRAM_URL . 'assets/css/dg-festival-program.css',
        [],
        DG_FESTIVAL_PROGRAM_VERSION
    );

    wp_enqueue_script(
        'dg-festival-program',
        DG_FESTIVAL_PROGRAM_URL . 'assets/js/dg-festival-program.js',
        [],
        DG_FESTIVAL_PROGRAM_VERSION,
        true
    );
}

function dg_festival_program_shortcode(): string
{
    dg_festival_program_enqueue_assets();

    ob_start();
    include DG_FESTIVAL_PROGRAM_PATH . 'templates/program.php';
    return ob_get_clean();
}

add_shortcode('dg_program', 'dg_festival_program_shortcode');

function dg_festival_program_admin_menu(): void
{
    add_menu_page(
        'DG Program',
        'DG Program',
        'edit_posts',
        'dg-festival-program',
        'dg_festival_program_render_admin_page',
        'dashicons-calendar-alt',
        26
    );
}

add_action('admin_menu', 'dg_festival_program_admin_menu');

function dg_festival_program_handle_admin_save(): void
{
    if (!current_user_can('edit_posts')) {
        wp_die(esc_html__('Nincs jogosultságod a program szerkesztéséhez.', 'dg-festival-program'));
    }

    check_admin_referer('dg_festival_program_save', 'dg_festival_program_nonce');

    $raw_venues = isset($_POST['dg_venues']) && is_array($_POST['dg_venues']) ? $_POST['dg_venues'] : [];
    $sanitized_venues = dg_festival_program_sanitize_venues($raw_venues);

    if (empty($sanitized_venues)) {
        $sanitized_venues = dg_festival_program_get_default_venues();
    }

    update_option(DG_FESTIVAL_PROGRAM_VENUES_OPTION, $sanitized_venues, false);

    $raw_types = isset($_POST['dg_program_types']) && is_array($_POST['dg_program_types']) ? $_POST['dg_program_types'] : [];
    $sanitized_types = dg_festival_program_sanitize_program_types($raw_types);

    update_option(DG_FESTIVAL_PROGRAM_TAGS_OPTION, $sanitized_types, false);

    $venues = [];

    foreach ($sanitized_venues as $venue) {
        $venues[$venue['id']] = $venue;
    }

    $types = [];

    foreach ($sanitized_types as $type) {
        $types[$type['id']] = $type;
    }

    $raw_schedule = isset($_POST['dg_schedule']) && is_array($_POST['dg_schedule']) ? $_POST['dg_schedule'] : [];
    update_option(DG_FESTIVAL_PROGRAM_OPTION, dg_festival_program_sanitize_schedule($raw_schedule, $venues, $types), false);

    wp_safe_redirect(add_query_arg([
        'page' => 'dg-festival-program',
        'updated' => '1',
    ], admin_url('admin.php')));
    exit;
}

add_action('admin_post_dg_festival_program_save', 'dg_festival_program_handle_admin_save');

function dg_festival_program_render_admin_page(): void
{
    if (!current_user_can('edit_posts')) {
        wp_die(esc_html__('Nincs jogosultságod a program szerkesztéséhez.', 'dg-festival-program'));
    }

    $venues = dg_festival_program_get_venues();
    $schedule = dg_festival_program_get_schedule();
    $types = dg_festival_program_get_program_types();
    ?>
    <div class="wrap dg-admin-wrap">
        <h1>DG Program szerkesztő</h1>

        <?php if (isset($_GET['updated']) && $_GET['updated'] === '1') : ?>
            <div class="notice notice-success is-dismissible">
                <p>Program mentve.</p>
            </div>
        <?php endif; ?>

        <p>Az első oszlop az időpont, utána a lent megadott helyszínek jelennek meg oszlopként. A tag-ek opcionálisak, bővíthetők és átírhatók. Több idősávon át tartó programnál add meg a vége időpontot.</p>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="dg_festival_program_save">
            <?php wp_nonce_field('dg_festival_program_save', 'dg_festival_program_nonce'); ?>

            <div class="dg-admin-toolbar">
                <button type="button" class="button button-secondary" data-dg-add-venue>Új helyszín</button>
                <button type="button" class="button button-secondary" data-dg-add-type>Új tag</button>
                <button type="button" class="button button-secondary" data-dg-add-row>Új időpont</button>
                <button type="submit" class="button button-primary">Program mentése</button>
            </div>

            <section class="dg-admin-panel">
                <h2>Helyszínek</h2>
                <div class="dg-admin-venues" data-dg-venues>
                    <?php foreach ($venues as $venue_index => $venue) : ?>
                        <?php dg_festival_program_render_admin_venue((string) $venue_index, $venue); ?>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="dg-admin-panel">
                <h2>Tag-ek</h2>
                <div class="dg-admin-types" data-dg-types>
                    <?php foreach ($types as $type_index => $type) : ?>
                        <?php dg_festival_program_render_admin_type((string) $type_index, $type); ?>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="dg-admin-panel">
                <h2>Időpontok és programok</h2>
                <div class="dg-admin-schedule" data-dg-schedule>
                    <?php foreach ($schedule as $row_index => $row) : ?>
                        <?php dg_festival_program_render_admin_row((int) $row_index, $row, $venues, $types); ?>
                    <?php endforeach; ?>
                </div>
            </section>

            <div class="dg-admin-toolbar dg-admin-toolbar-bottom">
                <button type="button" class="button button-secondary" data-dg-add-venue>Új helyszín</button>
                <button type="button" class="button button-secondary" data-dg-add-type>Új tag</button>
                <button type="button" class="button button-secondary" data-dg-add-row>Új időpont</button>
                <button type="submit" class="button button-primary">Program mentése</button>
            </div>
        </form>
    </div>

    <template id="dg-program-venue-template">
        <?php dg_festival_program_render_admin_venue('__venue_index__', ['id' => '', 'label' => '', 'color' => '#eef3f7', 'text_color' => '#0f2133']); ?>
    </template>

    <template id="dg-program-type-template">
        <?php dg_festival_program_render_admin_type('__type_index__', ['id' => '', 'label' => '']); ?>
    </template>

    <template id="dg-program-row-template">
        <?php dg_festival_program_render_admin_row('__row_index__', ['time' => '', 'note' => '', 'events' => []], $venues, $types); ?>
    </template>

    <template id="dg-program-event-template">
        <?php dg_festival_program_render_admin_event('__row_index__', '__event_index__', ['venue' => array_key_first($venues), 'title' => '', 'type' => '', 'end_time' => ''], $venues, $types); ?>
    </template>

    <style>
        .dg-admin-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            margin: 18px 0;
        }

        .dg-admin-toolbar-bottom {
            margin-top: 20px;
        }

        .dg-admin-panel {
            max-width: 1180px;
            margin: 0 0 18px;
            padding: 16px;
            border: 1px solid #dcdcde;
            background: #fff;
        }

        .dg-admin-panel h2 {
            margin-top: 0;
        }

        .dg-admin-venue,
        .dg-admin-type,
        .dg-admin-event {
            display: grid;
            grid-template-columns: minmax(180px, 1fr) 120px 120px auto;
            gap: 10px;
            align-items: end;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #dcdcde;
            background: #fbfbfb;
        }

        .dg-admin-row {
            margin: 0 0 16px;
            border: 1px solid #dcdcde;
            background: #fff;
        }

        .dg-admin-row-head {
            display: grid;
            grid-template-columns: 150px minmax(180px, 1fr) auto;
            gap: 12px;
            align-items: end;
            padding: 14px;
            border-bottom: 1px solid #dcdcde;
            background: #f6f7f7;
        }

        .dg-admin-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .dg-admin-field input[type="text"],
        .dg-admin-field input[type="time"],
        .dg-admin-field select {
            width: 100%;
            max-width: 100%;
        }

        .dg-admin-events {
            padding: 14px;
        }

        .dg-admin-event {
            grid-template-columns: 170px minmax(220px, 1fr) 220px 150px auto;
        }

        .dg-admin-actions,
        .dg-admin-event-actions,
        .dg-admin-row-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        @media (max-width: 900px) {
            .dg-admin-venue,
            .dg-admin-type,
            .dg-admin-row-head,
            .dg-admin-event {
                grid-template-columns: 1fr;
            }

            .dg-admin-actions,
            .dg-admin-row-actions,
            .dg-admin-event-actions {
                justify-content: flex-start;
            }
        }
    </style>

    <script>
        (function () {
            const venues = document.querySelector("[data-dg-venues]");
            const types = document.querySelector("[data-dg-types]");
            const schedule = document.querySelector("[data-dg-schedule]");
            const venueTemplate = document.getElementById("dg-program-venue-template");
            const typeTemplate = document.getElementById("dg-program-type-template");
            const rowTemplate = document.getElementById("dg-program-row-template");
            const eventTemplate = document.getElementById("dg-program-event-template");

            if (!venues || !types || !schedule || !venueTemplate || !typeTemplate || !rowTemplate || !eventTemplate) {
                return;
            }

            function nextIndex() {
                return Date.now().toString() + Math.floor(Math.random() * 1000).toString();
            }

            function bindVenue(venue) {
                venue.querySelectorAll("[data-dg-remove-venue]").forEach(function (button) {
                    button.addEventListener("click", function () {
                        venue.remove();
                    });
                });
            }

            function bindType(type) {
                type.querySelectorAll("[data-dg-remove-type]").forEach(function (button) {
                    button.addEventListener("click", function () {
                        type.remove();
                    });
                });
            }

            function bindRow(row) {
                row.querySelectorAll("[data-dg-add-event]").forEach(function (button) {
                    button.addEventListener("click", function () {
                        const rowIndex = row.getAttribute("data-row-index");
                        const html = eventTemplate.innerHTML
                            .replaceAll("__row_index__", rowIndex)
                            .replaceAll("__event_index__", nextIndex());

                        row.querySelector("[data-dg-events]").insertAdjacentHTML("beforeend", html);
                        bindEvent(row.querySelector("[data-dg-events] [data-dg-event]:last-child"));
                    });
                });

                row.querySelectorAll("[data-dg-remove-row]").forEach(function (button) {
                    button.addEventListener("click", function () {
                        row.remove();
                    });
                });

                row.querySelectorAll("[data-dg-event]").forEach(bindEvent);
            }

            function bindEvent(event) {
                event.querySelectorAll("[data-dg-remove-event]").forEach(function (button) {
                    button.addEventListener("click", function () {
                        event.remove();
                    });
                });
            }

            document.querySelectorAll("[data-dg-add-venue]").forEach(function (button) {
                button.addEventListener("click", function () {
                    const html = venueTemplate.innerHTML.replaceAll("__venue_index__", nextIndex());
                    venues.insertAdjacentHTML("beforeend", html);
                    bindVenue(venues.querySelector("[data-dg-venue]:last-child"));
                });
            });

            document.querySelectorAll("[data-dg-add-type]").forEach(function (button) {
                button.addEventListener("click", function () {
                    const html = typeTemplate.innerHTML.replaceAll("__type_index__", nextIndex());
                    types.insertAdjacentHTML("beforeend", html);
                    bindType(types.querySelector("[data-dg-type]:last-child"));
                });
            });

            document.querySelectorAll("[data-dg-add-row]").forEach(function (button) {
                button.addEventListener("click", function () {
                    const rowIndex = nextIndex();
                    const html = rowTemplate.innerHTML.replaceAll("__row_index__", rowIndex);
                    schedule.insertAdjacentHTML("beforeend", html);
                    bindRow(schedule.querySelector("[data-dg-row]:last-child"));
                });
            });

            venues.querySelectorAll("[data-dg-venue]").forEach(bindVenue);
            types.querySelectorAll("[data-dg-type]").forEach(bindType);
            schedule.querySelectorAll("[data-dg-row]").forEach(bindRow);
        })();
    </script>
    <?php
}

function dg_festival_program_render_admin_venue($venue_index, array $venue): void
{
    ?>
    <div class="dg-admin-venue" data-dg-venue>
        <input type="hidden" name="dg_venues[<?php echo esc_attr((string) $venue_index); ?>][id]" value="<?php echo esc_attr($venue['id'] ?? ''); ?>">
        <div class="dg-admin-field">
            <label>Helyszín neve</label>
            <input type="text" name="dg_venues[<?php echo esc_attr((string) $venue_index); ?>][label]" value="<?php echo esc_attr($venue['label'] ?? ''); ?>" placeholder="Pl. Nagyszínpad">
        </div>
        <div class="dg-admin-field">
            <label>Háttérszín</label>
            <input type="color" name="dg_venues[<?php echo esc_attr((string) $venue_index); ?>][color]" value="<?php echo esc_attr($venue['color'] ?? '#eef3f7'); ?>">
        </div>
        <div class="dg-admin-field">
            <label>Szövegszín</label>
            <input type="color" name="dg_venues[<?php echo esc_attr((string) $venue_index); ?>][text_color]" value="<?php echo esc_attr($venue['text_color'] ?? '#0f2133'); ?>">
        </div>
        <div class="dg-admin-actions">
            <button type="button" class="button button-link-delete" data-dg-remove-venue>Helyszín törlése</button>
        </div>
    </div>
    <?php
}

function dg_festival_program_render_admin_type($type_index, array $type): void
{
    ?>
    <div class="dg-admin-type" data-dg-type>
        <input type="hidden" name="dg_program_types[<?php echo esc_attr((string) $type_index); ?>][id]" value="<?php echo esc_attr($type['id'] ?? ''); ?>">
        <div class="dg-admin-field">
            <label>Tag neve</label>
            <input type="text" name="dg_program_types[<?php echo esc_attr((string) $type_index); ?>][label]" value="<?php echo esc_attr($type['label'] ?? ''); ?>" placeholder="Pl. Fő program">
        </div>
        <div></div>
        <div></div>
        <div class="dg-admin-actions">
            <button type="button" class="button button-link-delete" data-dg-remove-type>Tag törlése</button>
        </div>
    </div>
    <?php
}

function dg_festival_program_render_admin_row($row_index, array $row, array $venues, array $types): void
{
    $events = isset($row['events']) && is_array($row['events']) ? $row['events'] : [];
    ?>
    <section class="dg-admin-row" data-dg-row data-row-index="<?php echo esc_attr((string) $row_index); ?>">
        <div class="dg-admin-row-head">
            <div class="dg-admin-field">
                <label>Időpont</label>
                <input type="time" name="dg_schedule[<?php echo esc_attr((string) $row_index); ?>][time]" value="<?php echo esc_attr($row['time'] ?? ''); ?>">
            </div>
            <div class="dg-admin-field">
                <label>Időpont megjegyzés</label>
                <input type="text" name="dg_schedule[<?php echo esc_attr((string) $row_index); ?>][note]" value="<?php echo esc_attr($row['note'] ?? ''); ?>" placeholder="Pl. Kapunyitás">
            </div>
            <div class="dg-admin-row-actions">
                <button type="button" class="button" data-dg-add-event>Program hozzáadása</button>
                <button type="button" class="button button-link-delete" data-dg-remove-row>Időpont törlése</button>
            </div>
        </div>
        <div class="dg-admin-events" data-dg-events>
            <?php foreach ($events as $event_index => $event) : ?>
                <?php dg_festival_program_render_admin_event($row_index, (int) $event_index, $event, $venues, $types); ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

function dg_festival_program_render_admin_event($row_index, $event_index, array $event, array $venues, array $types): void
{
    $venue = $event['venue'] ?? array_key_first($venues);
    $type = $event['type'] ?? '';
    ?>
    <div class="dg-admin-event" data-dg-event>
        <div class="dg-admin-field">
            <label>Helyszín</label>
            <select name="dg_schedule[<?php echo esc_attr((string) $row_index); ?>][events][<?php echo esc_attr((string) $event_index); ?>][venue]">
                <?php foreach ($venues as $venue_key => $venue_data) : ?>
                    <option value="<?php echo esc_attr($venue_key); ?>" <?php selected($venue, $venue_key); ?>>
                        <?php echo esc_html($venue_data['label']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="dg-admin-field">
            <label>Program címe</label>
            <input type="text" name="dg_schedule[<?php echo esc_attr((string) $row_index); ?>][events][<?php echo esc_attr((string) $event_index); ?>][title]" value="<?php echo esc_attr($event['title'] ?? ''); ?>">
        </div>
        <div class="dg-admin-field">
            <label>Tag</label>
            <select name="dg_schedule[<?php echo esc_attr((string) $row_index); ?>][events][<?php echo esc_attr((string) $event_index); ?>][type]">
                <option value="" <?php selected($type, ''); ?>>Nincs tag</option>
                <?php foreach ($types as $type_key => $type_label) : ?>
                    <option value="<?php echo esc_attr($type_key); ?>" <?php selected($type, $type_key); ?>>
                        <?php echo esc_html($type_label['label']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="dg-admin-field">
            <label>Vége (opcionális)</label>
            <input type="time" name="dg_schedule[<?php echo esc_attr((string) $row_index); ?>][events][<?php echo esc_attr((string) $event_index); ?>][end_time]" value="<?php echo esc_attr($event['end_time'] ?? ''); ?>">
        </div>
        <div class="dg-admin-event-actions">
            <button type="button" class="button button-link-delete" data-dg-remove-event>Program törlése</button>
        </div>
    </div>
    <?php
}
