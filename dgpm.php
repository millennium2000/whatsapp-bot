<?php
/**
 * Plugin Name: DGPM Program
 * Description: WordPress shortcode for a configurable festival program schedule.
 * Version: 1.0.0
 * Author: Duna Group
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('DGPM_VERSION')) {
    define('DGPM_VERSION', '1.0.0');
}

define('DGPM_OPTION', 'dgpm_program_data');

function dgpm_default_data() {
    return array(
        'title' => 'I. DUNA GROUP CSALADI NAP',
        'subtitle' => 'Zanka - Program',
        'venues' => array(
            array('id' => 'nagyszinpad', 'label' => 'Nagyszinpad', 'color' => '#c9d7e6', 'text' => '#0f2133'),
            array('id' => 'szinhaz', 'label' => 'Szinhaz', 'color' => '#5f96cf', 'text' => '#0f2133'),
            array('id' => 'focipalya', 'label' => 'Focipalya', 'color' => '#2b72b8', 'text' => '#ffffff'),
            array('id' => 'sportcsarnok', 'label' => 'Sportcsarnok', 'color' => '#f2e799', 'text' => '#0f2133'),
            array('id' => 'sportkert', 'label' => 'Sportkert', 'color' => '#ffd200', 'text' => '#0f2133'),
            array('id' => 'mozi', 'label' => 'Mozi', 'color' => '#d4aa2c', 'text' => '#0f2133')
        ),
        'tags' => array(
            array('id' => 'fo', 'label' => 'Fo program'),
            array('id' => 'reg', 'label' => 'Regisztraciohoz kotott program'),
            array('id' => 'folyamatos', 'label' => 'Folyamatos program')
        ),
        'rows' => array(
            array('time' => '09:00', 'events' => array(array('venue' => 'nagyszinpad', 'title' => 'Erkezes, regisztracio', 'tag' => '', 'end' => ''))),
            array('time' => '10:30', 'events' => array(array('venue' => 'nagyszinpad', 'title' => 'Megnyito', 'tag' => 'fo', 'end' => ''))),
            array('time' => '11:00', 'events' => array(
                array('venue' => 'nagyszinpad', 'title' => 'Kalap Jakab koncert', 'tag' => 'fo', 'end' => ''),
                array('venue' => 'szinhaz', 'title' => 'Acs Fruzsina stand-up musor', 'tag' => 'fo', 'end' => '')
            )),
            array('time' => '16:30', 'events' => array(array('venue' => 'nagyszinpad', 'title' => 'EDDA Muvek koncert', 'tag' => 'fo', 'end' => ''))),
            array('time' => '18:30', 'events' => array(array('venue' => 'nagyszinpad', 'title' => 'Kapuzaras', 'tag' => '', 'end' => '')))
        )
    );
}

function dgpm_data() {
    $data = get_option(DGPM_OPTION);
    if (!is_array($data)) {
        $data = dgpm_default_data();
    }
    return $data;
}

function dgpm_slug($text) {
    $text = strtolower(remove_accents((string) $text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text ? $text : 'item-' . wp_rand(1000, 9999);
}

function dgpm_minutes($time) {
    if (!preg_match('/^(\d{1,2}):(\d{2})$/', (string) $time, $m)) {
        return null;
    }
    return ((int) $m[1] * 60) + (int) $m[2];
}

function dgpm_sanitize_data($raw) {
    $default = dgpm_default_data();
    $data = array();
    $data['title'] = sanitize_text_field(isset($raw['title']) ? $raw['title'] : $default['title']);
    $data['subtitle'] = sanitize_text_field(isset($raw['subtitle']) ? $raw['subtitle'] : $default['subtitle']);
    $data['venues'] = array();
    $venue_ids = array();

    if (!empty($raw['venues']) && is_array($raw['venues'])) {
        foreach ($raw['venues'] as $venue) {
            $label = sanitize_text_field(isset($venue['label']) ? $venue['label'] : '');
            if ($label === '') {
                continue;
            }
            $id = sanitize_key(isset($venue['id']) && $venue['id'] !== '' ? $venue['id'] : dgpm_slug($label));
            if ($id === '') {
                $id = dgpm_slug($label);
            }
            $base = $id;
            $i = 2;
            while (in_array($id, $venue_ids, true)) {
                $id = $base . '-' . $i;
                $i++;
            }
            $venue_ids[] = $id;
            $data['venues'][] = array(
                'id' => $id,
                'label' => $label,
                'color' => sanitize_hex_color(isset($venue['color']) ? $venue['color'] : '') ?: '#eef3f7',
                'text' => sanitize_hex_color(isset($venue['text']) ? $venue['text'] : '') ?: '#102b46'
            );
        }
    }
    if (empty($data['venues'])) {
        $data['venues'] = $default['venues'];
        $venue_ids = wp_list_pluck($data['venues'], 'id');
    }

    $data['tags'] = array();
    $tag_ids = array('' => true);
    if (!empty($raw['tags']) && is_array($raw['tags'])) {
        foreach ($raw['tags'] as $tag) {
            $label = sanitize_text_field(isset($tag['label']) ? $tag['label'] : '');
            if ($label === '') {
                continue;
            }
            $id = sanitize_key(isset($tag['id']) && $tag['id'] !== '' ? $tag['id'] : dgpm_slug($label));
            if ($id === '') {
                $id = dgpm_slug($label);
            }
            $base = $id;
            $i = 2;
            while (isset($tag_ids[$id])) {
                $id = $base . '-' . $i;
                $i++;
            }
            $tag_ids[$id] = true;
            $data['tags'][] = array('id' => $id, 'label' => $label);
        }
    }

    $data['rows'] = array();
    if (!empty($raw['rows']) && is_array($raw['rows'])) {
        foreach ($raw['rows'] as $row) {
            $time = sanitize_text_field(isset($row['time']) ? $row['time'] : '');
            if ($time === '') {
                continue;
            }
            $events = array();
            if (!empty($row['events']) && is_array($row['events'])) {
                foreach ($row['events'] as $event) {
                    $title = sanitize_text_field(isset($event['title']) ? $event['title'] : '');
                    if ($title === '') {
                        continue;
                    }
                    $venue = sanitize_key(isset($event['venue']) ? $event['venue'] : '');
                    if (!in_array($venue, $venue_ids, true)) {
                        $venue = $venue_ids[0];
                    }
                    $tag = sanitize_key(isset($event['tag']) ? $event['tag'] : '');
                    if (!isset($tag_ids[$tag])) {
                        $tag = '';
                    }
                    $events[] = array(
                        'venue' => $venue,
                        'title' => $title,
                        'tag' => $tag,
                        'end' => sanitize_text_field(isset($event['end']) ? $event['end'] : '')
                    );
                }
            }
            $data['rows'][] = array('time' => $time, 'events' => $events);
        }
    }
    usort($data['rows'], function($a, $b) { return strcmp($a['time'], $b['time']); });
    return $data;
}

function dgpm_admin_menu() {
    add_menu_page('DG Program', 'DG Program', 'manage_options', 'dgpm-program', 'dgpm_admin_page', 'dashicons-calendar-alt', 26);
}
add_action('admin_menu', 'dgpm_admin_menu');

function dgpm_handle_save() {
    if (!current_user_can('manage_options')) {
        wp_die('Nincs jogosultsag.');
    }
    check_admin_referer('dgpm_save');
    $raw = isset($_POST['dgpm']) && is_array($_POST['dgpm']) ? wp_unslash($_POST['dgpm']) : array();
    update_option(DGPM_OPTION, dgpm_sanitize_data($raw));
    wp_safe_redirect(admin_url('admin.php?page=dgpm-program&updated=1'));
    exit;
}
add_action('admin_post_dgpm_save', 'dgpm_handle_save');

function dgpm_admin_page() {
    $data = dgpm_data();
    $venues = $data['venues'];
    $tags = $data['tags'];
    ?>
    <div class="wrap dgpm-admin">
      <h1>DG Program</h1>
      <?php if (isset($_GET['updated'])) : ?><div class="notice notice-success"><p>Mentve.</p></div><?php endif; ?>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="dgpm_save">
        <?php wp_nonce_field('dgpm_save'); ?>
        <h2>Fejlec</h2>
        <p><input class="regular-text" name="dgpm[title]" value="<?php echo esc_attr($data['title']); ?>" placeholder="Cim"></p>
        <p><input class="regular-text" name="dgpm[subtitle]" value="<?php echo esc_attr($data['subtitle']); ?>" placeholder="Alcim"></p>
        <h2>Helyszinek</h2>
        <div id="dgpm-venues">
          <?php foreach ($venues as $i => $venue) : ?>
            <p class="dgpm-line"><input name="dgpm[venues][<?php echo (int) $i; ?>][id]" value="<?php echo esc_attr($venue['id']); ?>" placeholder="azonosito"> <input name="dgpm[venues][<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr($venue['label']); ?>" placeholder="nev"> <input type="color" name="dgpm[venues][<?php echo (int) $i; ?>][color]" value="<?php echo esc_attr($venue['color']); ?>"> <input type="color" name="dgpm[venues][<?php echo (int) $i; ?>][text]" value="<?php echo esc_attr($venue['text']); ?>"> <button type="button" class="button dgpm-remove">Torles</button></p>
          <?php endforeach; ?>
        </div>
        <p><button type="button" class="button" id="dgpm-add-venue">Helyszin hozzaadasa</button></p>
        <h2>Opcion?lis tagek</h2>
        <div id="dgpm-tags">
          <?php foreach ($tags as $i => $tag) : ?>
            <p class="dgpm-line"><input name="dgpm[tags][<?php echo (int) $i; ?>][id]" value="<?php echo esc_attr($tag['id']); ?>" placeholder="azonosito"> <input name="dgpm[tags][<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr($tag['label']); ?>" placeholder="nev"> <button type="button" class="button dgpm-remove">Torles</button></p>
          <?php endforeach; ?>
        </div>
        <p><button type="button" class="button" id="dgpm-add-tag">Tag hozzaadasa</button></p>
        <h2>Idopontok es programok</h2>
        <div id="dgpm-rows">
          <?php foreach ($data['rows'] as $ri => $row) : dgpm_admin_row($ri, $row, $venues, $tags); endforeach; ?>
        </div>
        <p><button type="button" class="button" id="dgpm-add-row">Idopont hozzaadasa</button></p>
        <?php submit_button('Mentes'); ?>
      </form>
    </div>
    <style>.dgpm-admin input{margin:3px}.dgpm-row{background:#fff;border:1px solid #ccd0d4;padding:12px;margin:12px 0}.dgpm-event{background:#f6f7f7;border:1px solid #dcdcde;padding:8px;margin:8px 0}.dgpm-line{display:flex;gap:6px;align-items:center;flex-wrap:wrap}</style>
    <script>
    (function(){
      var rowIndex = <?php echo (int) count($data['rows']); ?>;
      function removeBind(root){root.querySelectorAll('.dgpm-remove').forEach(function(b){b.onclick=function(){b.closest('.dgpm-line,.dgpm-event,.dgpm-row').remove();};});}
      removeBind(document);
      document.getElementById('dgpm-add-venue').onclick=function(){var i=Date.now();document.getElementById('dgpm-venues').insertAdjacentHTML('beforeend','<p class="dgpm-line"><input name="dgpm[venues]['+i+'][id]" placeholder="azonosito"> <input name="dgpm[venues]['+i+'][label]" placeholder="nev"> <input type="color" name="dgpm[venues]['+i+'][color]" value="#eef3f7"> <input type="color" name="dgpm[venues]['+i+'][text]" value="#102b46"> <button type="button" class="button dgpm-remove">Torles</button></p>');removeBind(document);};
      document.getElementById('dgpm-add-tag').onclick=function(){var i=Date.now();document.getElementById('dgpm-tags').insertAdjacentHTML('beforeend','<p class="dgpm-line"><input name="dgpm[tags]['+i+'][id]" placeholder="azonosito"> <input name="dgpm[tags]['+i+'][label]" placeholder="nev"> <button type="button" class="button dgpm-remove">Torles</button></p>');removeBind(document);};
      document.getElementById('dgpm-add-row').onclick=function(){var i=rowIndex++;document.getElementById('dgpm-rows').insertAdjacentHTML('beforeend',<?php echo wp_json_encode(dgpm_admin_row_html('__ROW__', array('time'=>'','events'=>array()), $venues, $tags)); ?>.replace(/__ROW__/g,i));removeBind(document);bindAddEvent(document);};
      function bindAddEvent(root){root.querySelectorAll('.dgpm-add-event').forEach(function(b){b.onclick=function(){var row=b.getAttribute('data-row');var box=b.closest('.dgpm-row').querySelector('.dgpm-events');var i=Date.now();box.insertAdjacentHTML('beforeend',<?php echo wp_json_encode(dgpm_admin_event_html('__ROW__', '__EVENT__', array(), $venues, $tags)); ?>.replace(/__ROW__/g,row).replace(/__EVENT__/g,i));removeBind(document);};});}
      bindAddEvent(document);
    }());
    </script>
    <?php
}

function dgpm_admin_row($ri, $row, $venues, $tags) { echo dgpm_admin_row_html($ri, $row, $venues, $tags); }
function dgpm_admin_row_html($ri, $row, $venues, $tags) {
    ob_start(); ?>
    <div class="dgpm-row">
      <p class="dgpm-line"><strong>Idopont</strong> <input type="time" name="dgpm[rows][<?php echo esc_attr($ri); ?>][time]" value="<?php echo esc_attr(isset($row['time']) ? $row['time'] : ''); ?>"> <button type="button" class="button dgpm-remove">Idopont torlese</button></p>
      <div class="dgpm-events"><?php if (!empty($row['events'])) { foreach ($row['events'] as $ei => $event) { echo dgpm_admin_event_html($ri, $ei, $event, $venues, $tags); } } ?></div>
      <p><button type="button" class="button dgpm-add-event" data-row="<?php echo esc_attr($ri); ?>">Program hozzaadasa</button></p>
    </div>
    <?php return ob_get_clean();
}
function dgpm_admin_event_html($ri, $ei, $event, $venues, $tags) {
    $venue = isset($event['venue']) ? $event['venue'] : '';
    $tag = isset($event['tag']) ? $event['tag'] : '';
    ob_start(); ?>
    <div class="dgpm-event">
      <p class="dgpm-line"><input class="regular-text" name="dgpm[rows][<?php echo esc_attr($ri); ?>][events][<?php echo esc_attr($ei); ?>][title]" value="<?php echo esc_attr(isset($event['title']) ? $event['title'] : ''); ?>" placeholder="Program neve">
      <select name="dgpm[rows][<?php echo esc_attr($ri); ?>][events][<?php echo esc_attr($ei); ?>][venue]"><?php foreach ($venues as $v) : ?><option value="<?php echo esc_attr($v['id']); ?>" <?php selected($venue, $v['id']); ?>><?php echo esc_html($v['label']); ?></option><?php endforeach; ?></select>
      <select name="dgpm[rows][<?php echo esc_attr($ri); ?>][events][<?php echo esc_attr($ei); ?>][tag]"><option value="">Nincs tag</option><?php foreach ($tags as $t) : ?><option value="<?php echo esc_attr($t['id']); ?>" <?php selected($tag, $t['id']); ?>><?php echo esc_html($t['label']); ?></option><?php endforeach; ?></select>
      Vege: <input type="time" name="dgpm[rows][<?php echo esc_attr($ri); ?>][events][<?php echo esc_attr($ei); ?>][end]" value="<?php echo esc_attr(isset($event['end']) ? $event['end'] : ''); ?>">
      <button type="button" class="button dgpm-remove">Torles</button></p>
    </div>
    <?php return ob_get_clean();
}

function dgpm_shortcode() {
    $data = dgpm_data();
    $venues = $data['venues'];
    $tags = array();
    foreach ($data['tags'] as $t) { $tags[$t['id']] = $t['label']; }
    $times = array();
    foreach ($data['rows'] as $r) { $times[] = $r['time']; }
    ob_start(); ?>
    <style>
    .dgpm,.dgpm *{box-sizing:border-box}.dgpm{font-family:Inter,Segoe UI,Arial,sans-serif;color:#1f2f3f;width:100%}.dgpm-head{text-align:center;margin-bottom:16px}.dgpm-title{font-size:24px;font-weight:700;color:#102b46}.dgpm-sub{font-size:13px;text-transform:uppercase;letter-spacing:.08em;color:#5d6f81}.dgpm-legend{display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-bottom:16px}.dgpm-chip{padding:7px 12px;border-radius:4px;border:1px solid rgba(16,43,70,.1);font-size:12px;font-weight:700}.dgpm-wrap{border:1px solid #d5dde6;border-radius:4px;overflow:hidden}.dgpm-table{width:100%;border-collapse:collapse;table-layout:fixed}.dgpm-table th{background:#102b46;color:#fff;padding:12px 8px;font-size:12px;text-transform:uppercase}.dgpm-table td{border:1px solid #dbe3eb;padding:10px 8px;text-align:center;vertical-align:middle;font-size:13px;line-height:1.35}.dgpm-time{background:#f7f9fb!important;font-weight:700;color:#102b46}.dgpm-event{border-radius:4px;padding:8px;font-weight:700}.dgpm-tag{display:block;margin-top:5px;font-size:11px;font-weight:600;opacity:.75}.dgpm-mobile{display:none}.dgpm-card{border:1px solid #dbe3eb;border-radius:4px;margin-bottom:12px;overflow:hidden}.dgpm-card-time{background:#102b46;color:#fff;font-size:19px;font-weight:700;padding:14px}.dgpm-card-body{padding:12px}.dgpm-card .dgpm-event{margin:7px 0}@media(max-width:900px){.dgpm-wrap{display:none}.dgpm-mobile{display:block}.dgpm-title{font-size:21px}}
    </style>
    <div class="dgpm">
      <div class="dgpm-head"><div class="dgpm-title"><?php echo esc_html($data['title']); ?></div><div class="dgpm-sub"><?php echo esc_html($data['subtitle']); ?></div></div>
      <div class="dgpm-legend"><?php foreach ($venues as $v) : ?><span class="dgpm-chip" style="background:<?php echo esc_attr($v['color']); ?>;color:<?php echo esc_attr($v['text']); ?>"><?php echo esc_html($v['label']); ?></span><?php endforeach; ?></div>
      <div class="dgpm-wrap"><table class="dgpm-table"><thead><tr><th>Idopont</th><?php foreach ($venues as $v) : ?><th><?php echo esc_html($v['label']); ?></th><?php endforeach; ?></tr></thead><tbody>
      <?php foreach ($data['rows'] as $ri => $row) : ?><tr><td class="dgpm-time"><?php echo esc_html($row['time']); ?></td><?php foreach ($venues as $v) : $printed=false; foreach ($row['events'] as $event) { if ($event['venue'] !== $v['id']) continue; $span=1; $start=dgpm_minutes($row['time']); $end=dgpm_minutes($event['end']); if ($start !== null && $end !== null && $end > $start) { for ($x=$ri+1;$x<count($times);$x++){ $nm=dgpm_minutes($times[$x]); if ($nm === null || $nm >= $end) break; $span++; } } ?><td rowspan="<?php echo (int) $span; ?>"><div class="dgpm-event" style="background:<?php echo esc_attr($v['color']); ?>;color:<?php echo esc_attr($v['text']); ?>"><?php echo esc_html($event['title']); ?><?php if ($event['tag'] && isset($tags[$event['tag']])) : ?><span class="dgpm-tag"><?php echo esc_html($tags[$event['tag']]); ?></span><?php endif; ?></div></td><?php $printed=true; break; } if (!$printed) : ?><td></td><?php endif; endforeach; ?></tr><?php endforeach; ?>
      </tbody></table></div>
      <div class="dgpm-mobile"><?php foreach ($data['rows'] as $row) : ?><div class="dgpm-card"><div class="dgpm-card-time"><?php echo esc_html($row['time']); ?></div><div class="dgpm-card-body"><?php foreach ($row['events'] as $event) : $v=null; foreach($venues as $vv){ if($vv['id']===$event['venue']){$v=$vv;break;} } if(!$v){$v=$venues[0];} ?><div class="dgpm-event" style="background:<?php echo esc_attr($v['color']); ?>;color:<?php echo esc_attr($v['text']); ?>"><?php echo esc_html($event['title']); ?><?php if ($event['tag'] && isset($tags[$event['tag']])) : ?><span class="dgpm-tag"><?php echo esc_html($tags[$event['tag']]); ?></span><?php endif; ?></div><?php endforeach; ?></div></div><?php endforeach; ?></div>
    </div>
    <?php return ob_get_clean();
}
add_shortcode('dg_program', 'dgpm_shortcode');
add_shortcode('dgpm_program', 'dgpm_shortcode');
