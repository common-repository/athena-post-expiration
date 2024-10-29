<?php

namespace Athena\Controller;

use Athena\Services\Cron;
use Athena\Utils\Constants;

/**
 * Builds the form fields for the setting and metadata pages
 *
 * @todo rewrite this class for cleaner code and easier management.
 *
 * @version 2.0.0
 * @author erik@brightthought.co
 */
class Fields
{
	private $_settings;

	/**
	 * Establishes the needed data for the field
	 *
	 * @param $options
	 * @param bool $saved
	 * @param bool $settings
	 */
	public function __construct($options, $saved = false, $settings = false)
	{
		if ( $settings !== false ) {
			$this->_settings = $settings;
		}

		$call = $options['type'];
		$this->$call($options, $saved);
	}


	private function header( $opt )
	{
		?>
        <h2><?= $opt['name']; ?></h2>
		<?php
	}

	private function date_box( $opt, $saved )
	{
		global $post;
		?>
        <div class="athena-element-hold">
            <label for="<?= $opt['id']; ?>" class="athena-title"><?= $opt['name']; ?></label>
            <div class="athena-desc"><?= $opt['desc']; ?></div>
            <input type="text" id="<?= $opt['id']; ?>" name="<?= $opt['id']; ?>" value="<?= athena_date_timezone($saved); ?>" class="athena-datetime form-control">
        </div>
		<?php
	}

	private function future_date($opt, $saved)
	{
		?>
        <div class="athena-element-hold">
            <div class="athena-title"><?= $opt['name']; ?></div>
            <div class="athena-desc"><?= $opt['desc']; ?></div>
            <div class="row">
                <div class="col-sm-4">
                    <select name="<?= $opt['id']; ?>[count]">
						<?php for($i = 1; $i <= 12; $i++) : ?>
                            <option value="<?= $i; ?>" <?= $saved['count'] == $i ? 'selected' : ''; ?>><?= $i; ?></option>
						<?php endfor;	?>
                    </select>
                </div>
                <div class="col-sm-8">
					<?php
					$type = $opt['times'] === 'day' ?
						['d' => 'Day(s)', 'w' => 'Week(s)', 'm' => 'Month(s)'] :
						['h' => 'Hour(s)', 'd' => 'Day(s)'];
					?>

                    <select name="<?= $opt['id']; ?>[type]">
						<?php foreach( $type as $key => $value ) : ?>
                            <option value="<?= $key; ?>" <?= $saved['type'] == $key ? 'selected' : ''; ?>><?= $value; ?></option>
						<?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

		<?php
	}

	private function text( $opt, $saved )
	{
		$timezone = get_option('timezone_string');

		echo $this->activate_check( $opt );
		?>
        <div class="athena-title"><?= $opt['name']; ?></div>
        <div class="athena-desc"><?= $opt['desc']; ?></div>
        <input type="text" name="<?= $opt['id']; ?>" value="<?= $saved; ?>" class="form-control" <?= ($opt['required'])? 'data-required="1"' : ''; ?> <?= isset($opt['max'])? 'maxlength="'. $opt['max'] .'"': ''; ?>>

		<?php

		if ( isset($opt['display']) && $opt['display'] === true ) {
			$dt = new \DateTime("now", new \DateTimeZone($timezone)); //first argument "must" be a string
			echo '<p>(' . $dt->format($saved) .') based on timezone: '. $timezone . ' (Change timezone <a href="/wp-admin/options-general.php#timezone_string">here</a>)</p>';
		}

		if ( isset($opt['legend']) ) {
			echo $opt['legend'];
		}
		echo $this->activate_close();
	}

	/**
	 * Create the textarea for the settings
	 *
	 * @param $opt
	 * @param $saved
	 *
	 * @return void
	 */
	private function textarea( $opt, $saved )
	{
		echo $this->activate_check( $opt );
		?>
        <div class="athena-title"><?= $opt['name']; ?></div>
        <div class="athena-desc"><?= $opt['desc']; ?></div>
		<?php wp_editor( $saved, $opt['id'], array('wpautop' => false)); ?>

		<?php
        if ( isset($opt['legend']) ) { echo $opt['legend']; }
        echo $this->activate_close();
	}

	/**
	 * Generate the checkbox element
	 *
	 * @param $opt
	 * @param $saved
	 *
	 * @return void
	 */
	private function checkbox( $opt, $saved )
	{
		echo $this->activate_check( $opt );
		?>
        <div class="athena-title"><?= $opt['name']; ?></div>
        <div class="athena-desc"><?= $opt['desc']; ?></div>

        <input type="checkbox" value="1" name="<?= $opt['id']; ?>" id="<?= $opt['id']; ?>" class="athena-checkbox" <?= ( isset($saved) && $saved ) ? 'checked' : ''; ?>>
        <label for="<?= $opt['id']; ?>"></label>
		<?php
		echo $this->activate_close();
	}

	private function category( $opt, $saved )
	{

		echo $this->activate_check( $opt );
		$args = array(
			'hide_empty' => false
		);
		$categories = get_categories( $args );
		?>

        <div class="athena-title"><?= $opt['name']; ?></div>
        <div class="athena-desc"><?= $opt['desc']; ?></div>
        <select name="<?= $opt['id']; ?>[]" class="athena-choices" multiple>
			<?php
			foreach($categories as $key => $value){

				echo '<option value="'.$value->term_id.'"';

				if(!empty($saved) && ($key = array_search($value->term_id, $saved)) !== false){

					echo 'selected="selected"';

				}

				echo '>'.$value->name.'</option>';

			}
			?>
        </select>
		<?php
		echo $this->activate_close();
	}

	private function select( $opt, $saved )
	{

		echo $this->activate_check( $opt );
		?>
        <div class="athena-title"><?= $opt['name']; ?></div>
        <div class="athena-desc"><?= $opt['desc']; ?></div>
        <select name="<?= $opt['id']; ?>" class="athena-choices" <?= (isset($opt['onChange']) && $opt['onChange'] === true) ? 'data-watch="' . $opt['class'] . '"' : ''; ?> <?= isset($opt['required']) ? 'data-required="1"' : ''; ?>>
			<?php
			foreach($opt['options'] as $key => $name){

				echo '<option value="'.$key.'"';

				if ($key === $saved){
					echo 'selected="selected"';
				}

				echo '>'. $name .'</option>';

			}
			?>
        </select>
		<?php
        echo $this->activate_close();
	}

	/**
	 * Displays the scheduled crons
	 *
	 * @param $opt
	 *
	 * @return void
	 */
	private function cron( $opt )
	{
		$jobs = Cron::getCronJobs();
		?>
        <div class="athena-element-hold">
			<?php if( ! empty($jobs) ) : ?>
				<?php
				foreach( $jobs as $k => $items ) {
                    $post_label = Constants::$ATHENA_POSTTYPES[$k];

					include(Constants::$ATHENA_DIR . '/resources/views/cronschedule.php');
				}
				?>
			<?php else : ?>
                <p>There are no expirations scheduled</p>
			<?php endif; ?>
        </div>
		<?php
	}

	/**
	 * Meta container for post focus
	 *
	 * @param $opt
	 * @param $saved
	 *
	 * @return void
	 */
	private function post_meta( $opt, $saved)
	{
		global $post_type;

		$args = array(
			'post_type' => 'athenaprofile',
			'posts_per_page' => -1,
			'meta_key' => '_post_expiration',
			'meta_value' => current_time('timestamp', 1),
			'meta_compare' => '>'

		);
		$profiles = get_posts( $args );
		?>
        <div class="athena-post_meta-hold">
			<?php $checked = ( isset($saved['enabled']) && $saved['enabled'] ) ? 'checked' : ''; ?>

            <div class="athena-enable">
                <span>Enable</span>
                <input type="checkbox" name="<?= $opt['id']; ?>[enabled]" value="1" <?= $checked; ?>>
            </div>
            <div class="athena-profile"><span>Profile</span>
                <select name="<?= $opt['id']; ?>[profile]" class="form-control">
                    <option value="">Select Profile</option>
					<?php foreach( $profiles as $value ) : ?>
                        <option
                                value="<?= $value->ID; ?>"
							<?= (isset($saved['profile']) && $value->ID == $saved['profile']) ? 'selected' : ''; ?>
                        ><?= $value->post_title; ?></option>
					<?php endforeach; ?>
                </select>
            </div>
            <div class="athena-sep-title">Or</div>

			<?php
			$count = $this->_settings['future_date_'.$post_type]['count'];
			$type = athena_date_time( $count );

			$time_string = strtotime('+'.$count . ' ' .$type[$this->_settings['future_date_'.$post_type]['type']]);
			?>
            <div class="athena-date-picker">
                <span>Date/Time</span>
                <div class="form-group">
                    <div class="input-group date" id="athena-post-datepicker">
                        <input type='text' name="<?= $opt['id']; ?>[timestamp]" value="<?= athena_date_timezone($saved['timestamp'] ?? $time_string); ?>" class="athena-datetime form-control" />
                        <span class="input-group-text">
								<i class="fas fa-calendar" aria-hidden="true"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
		<?php
	}

	/**
	 * Wraps elements that should be wrapped in an activation wrapper
	 *
	 * @param $opt
	 *
	 * @return string
	 */
	private function activate_check( $opt )
	{
		if ( isset($opt['activate']) ) {
			$class = ' athena-hide';
			$data = 'data-select="' . $opt['select'] . '"';
			$activate = 'data-activate="' . $opt['activate'] . '"';
			return '<div class="athena-element-hold'. $class . '"' . $data . ' ' . $activate .'>';
		}

		return '<div class="athena-element-hold">';
	}

	/**
     * Close the activate wrap
     *
	 * @return string
	 */
	private function activate_close ()
    {
		return '</div>';
	}
}
