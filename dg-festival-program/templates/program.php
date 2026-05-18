<?php
if (!defined('ABSPATH')) {
    exit;
}

$venues = dg_festival_program_get_venues();
$schedule = dg_festival_program_get_schedule();
$program_types = dg_festival_program_get_program_types();
$schedule_times = array_map(static function (array $row): string {
    return (string) ($row['time'] ?? '');
}, $schedule);

$get_event_rowspan = static function (array $event, int $row_index) use ($schedule_times): int {
    $start_time = $schedule_times[$row_index] ?? '';
    $end_time = (string) ($event['end_time'] ?? '');
    $start_minutes = dg_festival_program_time_to_minutes($start_time);
    $end_minutes = dg_festival_program_time_to_minutes($end_time);

    if ($start_minutes === null || $end_minutes === null || $end_minutes <= $start_minutes) {
        return 1;
    }

    $span = 1;
    $row_count = count($schedule_times);

    for ($next_index = $row_index + 1; $next_index < $row_count; $next_index++) {
        $next_minutes = dg_festival_program_time_to_minutes($schedule_times[$next_index]);

        if ($next_minutes === null || $next_minutes >= $end_minutes) {
            break;
        }

        $span++;
    }

    return $span;
};
?>

<div class="dg-program">
  <div class="dg-program-head">
    <div class="dg-program-title">I. DUNA GROUP CSALÁDI NAP</div>
    <div class="dg-program-subtitle">Zánka · Program</div>
  </div>

  <div class="dg-program-legend">
    <?php foreach ($venues as $venue) : ?>
      <div
        class="dg-legend-item dg-venue-chip dg-has-venue-tooltip"
        style="--dg-venue-bg: <?php echo esc_attr($venue['color']); ?>; --dg-venue-fg: <?php echo esc_attr($venue['text_color']); ?>;"
        data-venue-tooltip="<?php echo esc_attr($venue['label']); ?>"
        tabindex="0"
      >
        <?php echo esc_html($venue['label']); ?>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($program_types)) : ?>
    <div class="dg-program-type-legend">
      <?php foreach ($program_types as $type_key => $type) : ?>
        <span class="dg-program-type dg-program-type-<?php echo esc_attr($type_key); ?>">
          <?php echo esc_html($type['label']); ?>
        </span>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="dg-desktop-view">
    <div class="dg-program-table-wrap">
      <table class="dg-program-table dg-program-table-dynamic">
        <thead>
          <tr>
            <th class="dg-program-time-head">Időpont</th>
            <?php foreach ($venues as $venue) : ?>
              <th><?php echo esc_html($venue['label']); ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php $rowspan_blocks = []; ?>
          <?php foreach ($schedule as $row_index => $row) : ?>
            <?php
            $events_by_venue = [];

            foreach (($row['events'] ?? []) as $event) {
                if (empty($event['venue']) || empty($venues[$event['venue']])) {
                    continue;
                }

                $events_by_venue[$event['venue']][] = $event;
            }
            ?>
            <tr>
              <td class="time">
                <?php echo esc_html($row['time'] ?? ''); ?>
                <?php if (!empty($row['note'])) : ?>
                  <span class="dg-time-note"><?php echo esc_html($row['note']); ?></span>
                <?php endif; ?>
              </td>

              <?php foreach ($venues as $venue_key => $venue) : ?>
                <?php if (!empty($rowspan_blocks[$venue_key])) : ?>
                  <?php $rowspan_blocks[$venue_key]--; ?>
                  <?php continue; ?>
                <?php endif; ?>
                <?php $venue_events = $events_by_venue[$venue_key] ?? []; ?>
                <?php
                $rowspan = 1;

                foreach ($venue_events as $event) {
                    $rowspan = max($rowspan, $get_event_rowspan($event, (int) $row_index));
                }

                if ($rowspan > 1) {
                    $rowspan_blocks[$venue_key] = $rowspan - 1;
                }
                ?>
                <td
                  class="<?php echo esc_attr(!empty($venue_events) ? 'dg-venue-cell dg-has-venue-tooltip' : 'muted empty'); ?>"
                  <?php if ($rowspan > 1) : ?>
                    rowspan="<?php echo esc_attr((string) $rowspan); ?>"
                  <?php endif; ?>
                  <?php if (!empty($venue_events)) : ?>
                    style="--dg-venue-bg: <?php echo esc_attr($venue['color']); ?>; --dg-venue-fg: <?php echo esc_attr($venue['text_color']); ?>;"
                    data-venue-tooltip="<?php echo esc_attr($venue['label']); ?>"
                    tabindex="0"
                  <?php endif; ?>
                >
                  <?php foreach ($venue_events as $event) : ?>
                    <?php $type = $event['type'] ?? ''; ?>
                    <div class="dg-program-event">
                      <span class="dg-program-event-title"><?php echo esc_html($event['title']); ?></span>
                      <?php if (!empty($event['end_time'])) : ?>
                        <span class="dg-program-time-range"><?php echo esc_html(($row['time'] ?? '') . '–' . $event['end_time']); ?></span>
                      <?php endif; ?>
                      <?php if ($type !== '' && !empty($program_types[$type])) : ?>
                        <span class="dg-program-type dg-program-type-<?php echo esc_attr($type); ?>">
                          <?php echo esc_html($program_types[$type]['label']); ?>
                        </span>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="dg-mobile-view">
    <?php foreach ($schedule as $row) : ?>
      <?php
      $events_by_venue = [];

      foreach (($row['events'] ?? []) as $event) {
          if (empty($event['venue']) || empty($venues[$event['venue']])) {
              continue;
          }

          $events_by_venue[$event['venue']][] = $event;
      }
      ?>
      <div class="dg-mobile-card" data-time="<?php echo esc_attr($row['time'] ?? ''); ?>">
        <div class="dg-mobile-time">
          <?php echo esc_html($row['time'] ?? ''); ?>
          <?php if (!empty($row['note'])) : ?>
            <span class="dg-mobile-time-note"><?php echo esc_html($row['note']); ?></span>
          <?php endif; ?>
        </div>

        <?php foreach ($venues as $venue_key => $venue) : ?>
          <?php if (empty($events_by_venue[$venue_key])) : ?>
            <?php continue; ?>
          <?php endif; ?>

          <div class="dg-mobile-section">
            <div class="dg-mobile-label"><?php echo esc_html($venue['label']); ?></div>
            <div class="dg-tag-row">
              <?php foreach ($events_by_venue[$venue_key] as $event) : ?>
                <?php $type = $event['type'] ?? ''; ?>
                <div
                  class="dg-tag dg-venue-chip"
                  style="--dg-venue-bg: <?php echo esc_attr($venue['color']); ?>; --dg-venue-fg: <?php echo esc_attr($venue['text_color']); ?>;"
                >
                  <span class="dg-program-event-title"><?php echo esc_html($event['title']); ?></span>
                  <?php if (!empty($event['end_time'])) : ?>
                    <span class="dg-program-time-range"><?php echo esc_html(($row['time'] ?? '') . '–' . $event['end_time']); ?></span>
                  <?php endif; ?>
                  <?php if ($type !== '' && !empty($program_types[$type])) : ?>
                    <span class="dg-program-type dg-program-type-<?php echo esc_attr($type); ?>">
                      <?php echo esc_html($program_types[$type]['label']); ?>
                    </span>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
