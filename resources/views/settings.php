<?php
/**
 *
 * Settings view for the application
 *
 */
?>

<div class="athena-wrapper wrap">
	<h2>Settings - Athena</h2>
	<div class="athena-content-wrapper">
		<div class="athena-body clearfix">
			<aside>
				<div class="athena-aside-header">
					<img src="<?= \Athena\Utils\Constants::$ATHENA_URI;?>resources/dist/images/Athena-Logo.png" alt="Athena Logo">
				</div>
				<ul class="athena-menu">
					<li><a href="#" data-trigger="general" class="active">General</a></li>

					<?php foreach($this->post_types as $post) : ?>
						<li><a href="#" data-trigger="<?= $post->name; ?>"><?= $post->label; ?></a></li>
					<?php endforeach; ?>

					<li><a href="#" data-trigger="scheduled">Scheduled</a></li>
				</ul>
			</aside>
			<main>
				<form method="post" id="athena-settings" class="clearfix">
					<div class="athena-save-bar">
						<div class="athena-save">
							<div class="athena-saving">
								<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>
								<span class="sr-only">Saving</span>
							</div>
							<div class="athena-save-message"></div>
						</div>
						<input type="submit" value="Save" class="submit">
						<input type="reset" value="Reset" class="reset">
					</div>
					<div class="athena-form-inner">
						<div id="general" class="active">
							<?php
							foreach ( $this->fields['general'] as $field ) {
								if ( $field['type'] !== 'header' ) {
									$s = $this->saved[ $field['id'] ] ?? $field['default'];
									new \Athena\Controller\Fields($field, $s, $this->saved);
								} else {
									new \Athena\Controller\Fields($field, null, $this->saved);
								}
							}
							?>
						</div>
						<?php foreach($this->post_types as $post) : ?>
							<div id="<?= $post->name; ?>">

								<?php foreach($this->post_type_fields($post->name, $post->label) as $field) :
									if ($field['type'] !== 'header') {
										$s = $this->saved[ $field['id'] ] ?? $field['default'];
										new \Athena\Controller\Fields($field, $s, $this->saved);
									} else {
										new \Athena\Controller\Fields($field, null, $this->saved);
									}

								endforeach;	?>
							</div>
						<?php endforeach; ?>

						<div id="scheduled">
							<?php foreach( $this->fields['schedule'] as $field ) :
								new \Athena\Controller\Fields( $field, null, $this->saved );
							endforeach; ?>
						</div>
					</div>
				</form>
			</main>
		</div>
	</div>
</div>
