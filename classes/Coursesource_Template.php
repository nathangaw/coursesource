<?php

class Coursesource_Template {

	/**
	 * Directory name of where the templates are stored in a theme.
	 *
	 * @var string
	 */
	public const THEME_TEMPLATE_FOLDER = 'coursesource';


	/**
	 * @param       $template_path
	 * @param Array $data
	 *
	 * @return void
	 */
	public static function template( $template_path, $data ) {
		extract( $data, EXTR_OVERWRITE );
		$original_template = COURSESOURCE_PLUGIN_BASE . "templates/" . $template_path . ".php";
		$theme_path        = get_template_directory();
		$override_template = $theme_path . "/" . self::THEME_TEMPLATE_FOLDER . "/" . $template_path . ".php";

		if ( file_exists( $override_template ) ) {
			include( $override_template );
		}
		else {
			include( $original_template );
		}

	}

	/**
	 * Fetch a template a return the response
	 *
	 * @param       $template_path
	 * @param Array $data
	 *   *
	 *
	 * @return false|string
	 */
	public static function get_template( $template_path, $data = null ) {
		ob_start();
		self::template( $template_path, $data );
		return ob_get_clean();
	}

}
