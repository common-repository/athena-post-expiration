<?php
/**
 * Displays the cron job scheduled to run
 *
 * @var object $post_label
 * @var array $items
 */
?>
<div class="cron-type-wrapper">
    <h3><?= ucfirst($post_label->label); ?>(s) Scheduled</h3>
    <table class="table">
        <thead class="table-light">
        <th>Name</th>
        <th>Expires</th>
        <th>Expiration Type</th>
        </thead>
        <tbody>
		<?php foreach( $items as $t => $i ) : ?>
            <tr>
                <td><?= $i->post_title; ?></td>
                <td><?= athena_date_timezone($t); ?></td>
                <td><?= ucfirst($i->meta); ?></td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>
</div>
