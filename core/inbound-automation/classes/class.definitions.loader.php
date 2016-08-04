<?php

/**
 *  This class loads supporting automation definitions and listeners
 *  Contributors: Hudson Atwell
 */

if ( !class_exists( 'Inbound_Automation_Loader' ) ) {


	class Inbound_Automation_Loader {

		public static $instance; /* data object will hold all definitions below */
		public static $triggers; /* dataset containing defined trigger hooks */
		public static $rule; /* dataset containing rule settings */
		public static $rules; /* dataset containing inbound automation rules */
		public static $compare_options; /* array of acceptable comparision options */
		public static $argument_filters; /* dataset of agrument filters */
		public static $db_lookup_filters;
		public static $actions;
		public static $inbound_arguments; /* dataset that holds information on registered hooks */

		/**
		 *  Initialize singleton class
		 */
		public static function init() {

			/* return class instance if alredy defined */
			if ( isset(self::$instance) ) {
				return self::$instance;
			}

			//global $inbound_sid;
			//error_log($inbound_sid);

			/* create object */
			self::$instance = new stdClass();

			/* Load Rules */
			self::load_rules();
			self::load_arguments();
			self::load_compare_options();

			/* Load Automation Setting Definitions */
			self::define_triggers();
			self::define_arguments();
			self::define_actions();
			self::define_db_lookup_filters();

			/* Add Trigger Listeners to Hooks */
			self::add_trigger_listeners();

			return self::$instance;
		}

		/**
		 *  Load Rules from CPT and set them into static variatiable
		 */
		public static function load_rules() {

			$rules = get_posts( array (
					'post_type'=> 'automation',
					'post_status' => 'publish',
					'posts_per_page' => -1
				)
			);


			self::$instance->rules = ($rules) ? $rules : array();

		}

		/**
		 *  Loads rule settings
		 * @param INT $rule_id
		 */
		public static function load_rule( $rule_id ) {
			self::$rule = get_post_meta( $rule_id , 'inbound_rule' , true );
			self::$rule['ID'] = $rule_id;
		}

		/**
		 *  Loads hook argument data from wp_options
		 */
		public static function load_arguments() {
			$inbound_arguments = Inbound_Options_API::get_option( 'inbound_automation' , 'arguments' );

			self::$instance->inbound_arguments = ( $inbound_arguments  ) ?  $inbound_arguments : array();
		}

		/**
		 *  Loads possible compare options for filtering
		 */
		public static function load_compare_options() {

			/* Build Compare Options */
			$compare_args 	= 	array(
				'greater-than' => __( 'Greater Than', 'inbound-automation' ),
				'greater-than-equal-to' => __( 'Greater Than Or Equal To' , 'inbound-automation' ),
				'less-than' => __( 'Less Than' , 'inbound-automation' ),
				'less-than-equal-to' => __( 'Less Than Or Equal To' , 'inbound-automation' ),
				'contains' =>__( 'Contains', 'inbound-automation' ),
				'equals' => __( 'Equals', 'inbound-automation' )
			);

			/* Extend Compare Options */
			self::$instance->compare_options = apply_filters('inbound_automation_compare_args' , $compare_args );

		}


		/**
		 *  Load Triggers into Static Variable
		 */
		public static function define_triggers() {
			self::$instance->triggers = apply_filters( 'inbound_automation_triggers' , array() );
		}

		/**
		 *  	Define Argument Filters
		 */
		public static function define_arguments() {

			self::$instance->argument_filters = array();
			//error_log(print_r(self::$instance->triggers));
			//error_log(print_r(self::$instance->inbound_arguments , true));
			//exit;
			/* Loop Through Trigger Arguments & Build Trigger Filter Setting Array */
			foreach ( self::$instance->triggers as $hook =>$trigger) {

				/* Load Historic Arugment Keys Associated With Trigger Hook */
				$args = ( isset(self::$instance->inbound_arguments[ $hook ]) ) ? self::$instance->inbound_arguments[ $hook ]  : array();

				foreach ($trigger['arguments'] as $id => $argument ) {

					$keys = array();

					if (isset($args[$id]) && is_array($args[$id])) {

						$count = count($args[$id]);
						$c = 0;
						foreach ($args[$id] as $k => $v ) {
							if (is_array($v) || is_numeric($k)){
								$c++;
								$v = json_encode($v);
							}

							$keys[$id.':'.$k] = $k . ' ('.$v.')';
						}

						if ($c == $count) {
							$keys[$id] = $argument['label'] . ' ('.json_encode($args[$id]).')';
						}

					} else if (isset($args[$id])) {
						$keys[$id] = $argument['label'] . ' ('.$args[$id].')';
					}

					if (!isset($keys[$id]) || !$keys[$id]) {
						$keys[$id] = $argument['label'];
					}


					//error_log(print_r($keys,true));


					/* Build Trigger Filter Data */
					self::$instance->argument_filters[ $hook ][$argument['id']] = array(
						'id' => $argument['id'],
						'label' => $argument['label'],
						'key_input_type' => 'dropdown',
						'keys' => $keys,
						'compare' => self::$instance->compare_options ,
						'value_input_type' => 'text',
						'values' => false
					);
				}
			}
			//exit;
			self::$instance->argument_filters = apply_filters( 'inbound_automation_arguments' , self::$instance->argument_filters );
		}


		/* Define DB Lookup Filters */
		public static function define_db_lookup_filters() {

			$db_lookup_filters = array();
			$loaded = array();


			/* Loop Through Trigger Arguments & Build Filter Setting Array */
			foreach ( self::$instance->triggers as $hook =>$trigger) {

				if ( !isset($trigger['db_lookup_filters']) ) {
					continue;
				}

				foreach ($trigger['db_lookup_filters'] as $db_lookup_filter ) {

					/* Make sure a class referrence exists in db lookup options */
					if ( !isset($db_lookup_filter['class_name']) ) {
						continue;
					}

					/* Get Class Name */
					$class = $db_lookup_filter['class_name'];

					/* Load Queries Only Once */
					if ( array_key_exists( $db_lookup_filter['id'] , $db_lookup_filters ) ) {
						continue;
					}

					/* Load Available Queries DB Lookup Class */
					$keys = $class::get_key_map();

					if ( !$keys ) {
						$keys = array( '-1' => 'No Options Detected' );
					}

					/* Build Trigger Filter Data */
					$db_lookup_filters[$db_lookup_filter['id']] = array(
						'class_name' => $db_lookup_filter['class_name'],
						'id' => $db_lookup_filter['id'],
						'label' => $db_lookup_filter['label'],
						'key_input_type' => 'dropdown',
						'keys' => $keys,
						'compare' => self::$instance->compare_options ,
						'value_input_type' => 'text',
						'values' => false
					);

				}
			}

			self::$instance->db_lookup_filters = apply_filters( 'inbound_automation_db_lookup_filters' , $db_lookup_filters );

		}

		/**
		 *  Source actions from hook
		 */
		public static function define_actions() {
			self::$instance->actions = apply_filters( 'inbound_automation_actions' , array() );
		}

		/* Adds Listener Hooks for Building Filters and Rule Processings */
		public static function add_trigger_listeners() {
			foreach (self::$instance->triggers as $hook_name => $trigger) {
				if ( isset($trigger['action_hook']) ) {
					add_action( $trigger['action_hook'] , array( __CLASS__ , 'process_trigger' ) , 10 , count($trigger['arguments']) ) ;
				}
			}
		}

		/**
		 *  Checks a fired trigger for a match and schedules job
		 */
		public static function process_trigger() {

			$trigger = current_filter();
			$args = func_get_args();

			foreach (self::$instance->rules  as $rule) {

				self::load_rule( $rule->ID );
				$rule->settings = self::$rule;

				if ( !isset( self::$rule['trigger']) ) {
					continue;
				}

				if ( self::$rule['trigger'] == $trigger ) {

					$evaluate = true;
					$evals = array();

					$arguments = self::generate_arguments( $trigger , $args );


					/* Check Trigger Filters */
					if ( isset( self::$rule['trigger_filters'] )  && self::$rule['trigger_filters'] ) {

						foreach( self::$rule['trigger_filters'] as $filter) {
							if (strstr( $filter['trigger_filter_key'] , ':')) {
								$parts = explode(':', $filter['trigger_filter_key']);
								$target_argument = $arguments[$parts[0]][$parts[1]];
							} else {
								$target_argument = $arguments[$filter['trigger_filter_id']];
							}


							$evals[] = self::evaluate_trigger_filter( $filter , $target_argument );
						}

						/* Check Evalaution Nature for Final Decision */
						$evaluate = self::evaluate_arguments( self::$rule['trigger_filters_evaluate'] , $evals );
					}

					/* Log Event */
					self::record_trigger_event( $rule , $arguments , $trigger , $evaluate , $evals , self::$rule['trigger_filters_evaluate'] );

					/* Add Job to Queue if Passes Trigger Filters */
					if ( $evaluate  ) {
						/* Log Evaluation Message */
						self::record_schedule_event( $rule , $arguments , $trigger , $evaluate );

						Inbound_Automation_Processing::add_job_to_queue( self::$rule , $arguments );
					}
				}
			}

		}

		/*
		* Evaluate All Filters Based on Evaluation Condition
		*/
		public static function evaluate_arguments( $eval_nature , $evals ) {

			switch ($eval_nature) {
				case 'match-any' :
					foreach ( $evals as $eval ) {
						if ($eval['eval']) {
							$evaluate = true;
							break;
						} else {
							$evaluate = false;
						}
					}

					BREAK;

				case 'match-all' :
					foreach ( $evals as $eval ) {
						if ($eval['eval']) {
							$evaluate = true;
						} else {
							$evaluate = false;
						}
					}

					BREAK;

				case 'match-none' :
					foreach ( $evals as $eval ) {
						if ($eval['eval']) {
							$evaluate = false;
							break;
						} else {
							$evaluate = true;

						}
					}

					BREAK;
			}

			return $evaluate;
		}


		/*
		* Get Argument Key From Trigger
		*/
		public static function get_argument_key_from_trigger( $argument , $trigger ) {

			foreach ( self::$instance->triggers[$trigger]['arguments'] as $key => $arg ) {

				if ( $argument['trigger_filter_id'] == $arg['id'] ) {
					return $key;
				}
			}

		}

		/*
		* Evaluate Filter By Comparing Filter with Corresponding Incoming Data
		*/
		public static function evaluate_trigger_filter( $filter , $target_argument ) {
			//error_log(print_r($target_argument,true));
			if (!is_array($target_argument)) {
				$arg = $target_argument;
				$target_argument = array();
				$target_argument[ $filter['trigger_filter_key'] ] = $arg;
			} else {
				$target_argument[ $filter['trigger_filter_key'] ] = Inbound_Automation_Loader::flatten_array( $target_argument );
			}
			//error_log(print_r($target_argument,true));

			$compare_value = ( isset($target_argument[ $filter['trigger_filter_key'] ]) ) ? $target_argument[ $filter['trigger_filter_key'] ] : __('notset','inbound-pro');
			$eval = false;

			if (isset($target_argument[ $filter['trigger_filter_key'] ])) {
				switch ($filter['trigger_filter_compare']) {

					case 'greater-than' :
						if ( $filter['trigger_filter_value'] < $compare_value ) {
							$eval = true;
						}
						BREAK;
					case 'greater-than-equal-to' :
						if ( $filter['trigger_filter_value'] <= $compare_value ) {
							$eval = true;
						}
						BREAK;
					case 'less-than' :
						if ( $filter['trigger_filter_value'] > $compare_value ) {
							$eval = true;
						}
						BREAK;
					case 'less-than-equal-to' :
						if ( $filter['trigger_filter_value'] >= $compare_value ) {
							$eval = true;
						}
						BREAK;

					case 'contains' :
						if ( stristr( $compare_value , $filter['trigger_filter_value'] ) ) {
							$eval = true;
						}
						BREAK;

					case 'equals' :

						if (  $filter['trigger_filter_value'] == $compare_value ) {
							$eval = true;
						}
						BREAK;
				}
			}

			return array(
				'filter_key' => $filter['trigger_filter_key'] ,
				'filter_compare' => $filter['trigger_filter_compare'],
				'filter_value' => $filter['trigger_filter_value'],
				'compare_value' => $compare_value ,
				'eval' => $eval
			);

		}

		/*
		* Record Trigger Event in Logs
		*/
		public static function record_trigger_event( $rule , $arguments , $trigger , $evaluate , $evals , $eval_nature ) {

			$evaluate = (!$evaluate) ? __( 'Blocked' , 'ma' ) : __( 'Passed' , 'ma' );

			$message = "<h2>".__( 'Trigger' , 'inbound-pro' ). "</h2>";
			$message .= "<br><pre>" . $trigger . '</pre></p>';
			$message .= "<h2>". __( 'Trigger Filter Evaluation' , 'inbound-pro' ) ."</h2>";
			$message .= "<p>". __('Evaluation Result:' , 'inbound-pro' ) . "<br><pre>" . $evaluate ."</pre></p>";
			$message .= "<h2>". __('Evaluation Condition:' , 'inbound-pro' ) . "</h2><br><pre>" . $eval_nature . "</pre></p>";
			$message .= "<p>". __('Evaluation Details:' , 'inbound-pro' ). "<br><pre>" . print_r( $evals , true ) . "</pre></p>";
			$message .= "<p><h2>". __('Rule Settings:' , 'inbound-pro' ) ."</h2> <br> <pre>". print_r( $rule , true ) . "</pre></p>";
			$message .= "<p><h2>". __('Trigger Data:' , 'inbound-pro' ) ."</h2> <br> <pre>". print_r( $arguments , true) . '</pre></p>';


			inbound_record_log( __( 'Trigger Fired' , 'inbound-pro' ) , $message , $rule->ID , '-',  'trigger_event' );
		}

		/*
		* Record Schedule Event in Logs
		* @param OBJECT $rule contains information about rule being ran
		* @param ARRAY $arguments contains trigger(hook) argument data
		*/
		public static function record_schedule_event( $rule , $arguments ) {

			$message = "<p><h2>". __('Rule Settings:' , 'inbound-pro' )."</h2> <br> <pre>". print_r( $rule , true ) . '</pre></p>';
			$message .= "<p><h2>". __('Trigger Data:' , 'inbound-pro' )."</h2> <br> <pre>". print_r( $arguments , true ) . '</pre></p>';


			inbound_record_log( __( 'Scheduling Job' , 'inbound-pro' ) , $message , $rule->ID , '-', 'schedule_event' );
		}

		/**
		 * This method creates a key->value data map of data being passed from a trigger hook
		 * This data is used to assist in designing filters for triggers
		 */
		public static function generate_arguments( $hook, $args) {


			/* loop through arguments and update memory with available data with latest submission */
			$argument_definitions = self::$instance->triggers[$hook]['arguments'];

			foreach ($args as $key => $argument) {

				/* Get first argument definition in the definition array */
				$definition = array_shift($argument_definitions);

				/* if there is a call back run it */
				if (isset($definition['callback'])) {
					if (is_array($definition['callback']) ) {
						$argument = $definition['callback'][0]::$definition['callback'][1]( $argument );
					}
				}

				/* Place argument data into memory */
				$updated_arg_data = self::prepare_mixed_data($argument);

				if (isset(self::$instance->inbound_arguments[$hook][ $definition['id'] ]) && is_array(self::$instance->inbound_arguments[$hook][ $definition['id'] ]) ) {
					self::$instance->inbound_arguments[$hook][ $definition['id'] ] = array_replace( self::$instance->inbound_arguments[$hook][ $definition['id'] ] , $updated_arg_data );
				} else {
					self::$instance->inbound_arguments[$hook][ $definition['id'] ] = $updated_arg_data;
				}
			}

			/* update inbound arguments dataset with new data */
			self::update_arguments();

			/* return arguments */
			return self::$instance->inbound_arguments[$hook];

		}

		/**
		 * Takes a multidimensional argument array and flattens it
		 */
		public static function flatten_array( $array ) {
			$flatten = array();

			foreach ($array as $k => $value ) {
				if (is_numeric($k)) {
					continue;
				}

				if (is_array($value)) {
					foreach ($value as $k1 => $v1) {
						if (is_array($v1)) {
							$v1 = json_encode($v1);
						}
						$flatten[$k . ':' . $k1] = $v1;
					}
				} else {
					$flatten[$k] = $value;
				}
			}

			if (!$flatten) {
				$flatten = json_encode($array);
			}

			return $flatten;
		}

		/**
		 *  Updates the dataset that contains information on our tracked hooks
		 */
		public static function update_arguments() {
			if (self::$instance->inbound_arguments) {
				//error_log(print_r( self::$instance->inbound_arguments,true));
				Inbound_Options_API::update_option( 'inbound_automation' , 'arguments' , self::$instance->inbound_arguments );
			}
		}

		/**
		 * checks if json
		 */
		public static function prepare_mixed_data( $mixed ) {

			if (is_array($mixed)) {
				foreach ($mixed as $key=>$value) {
					$mixed[$key] = self::prepare_mixed_data( $value );
				}
				return $mixed;
			}

			/* check if json */
			json_decode( stripslashes($mixed) );
			if ( json_last_error() == JSON_ERROR_NONE ) {
				return json_decode( $mixed , true );
			}

			/* check if parse string */
			if ( strstr( $mixed , '=' ) && !strstr( trim($mixed) , ' ') ) {
				parse_str( $mixed , $matches );
				if ( count ($matches) > 1 ) {
					return $matches;
				}
			}

			/* return normal string */
			return $mixed;

		}


	}

	/**
	 *  Loads inbound automation defintitions into init at priority 20
	 */
	function inbound_automation_load_definitions() {
		$GLOBALS['Inbound_Automation_Loader'] = Inbound_Automation_Loader::init();
		return $GLOBALS['Inbound_Automation_Loader'];
	}

	/**
	 *  load loader in init when not running ajax
	 */
	add_action( 'init' , 'inbound_automation_load_definitions' , 1 );

	/* for debugging */
	$GLOBALS['inbound_sid'] = rand( 100 , 200 );
}

