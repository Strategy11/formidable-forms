<?php
if ( !defined('ABSPATH') ) die('You are not allowed to call this page directly.');

class FrmXMLHelper{

    public static function get_xml_values($opt, $padding){
        if(is_array($opt)){
            foreach($opt as $ok => $ov){
                echo "\n". $padding;
                echo '<'. (is_numeric($ok) ? 'key:' : '') . $ok .'>';
                self::get_xml_values($ov, $padding .'    ');
                if(is_array($ov))
                    echo "\n". $padding;
                echo '</'. (is_numeric($ok) ? 'key:' : '') . $ok .'>';
            }
        }else{
            echo self::cdata($opt);
        }
    }

    public static function import_xml($file){
        $defaults = array(
            'forms' => 0, 'fields' => 0, 'terms' => 0,
            'posts' => 0, 'views' => 0, 'actions' => 0,
            'styles' => 0,
        );

        $imported = array(
            'imported' => $defaults,
            'updated' => $defaults,
            'forms' => array(),
        );

        unset($defaults);

        if ( !defined('WP_IMPORTING') ) {
            define('WP_IMPORTING', true);
        }

        if ( !class_exists('DOMDocument') ) {
            return new WP_Error( 'SimpleXML_parse_error', __( 'Your server does not have XML enabled', 'formidable' ), libxml_get_errors() );
        }

        $dom = new DOMDocument;
		$success = $dom->loadXML( file_get_contents( $file ) );
		if ( ! $success ) {
			return new WP_Error( 'SimpleXML_parse_error', __( 'There was an error when reading this XML file', 'formidable' ), libxml_get_errors() );
		}

		$xml = simplexml_import_dom( $dom );
		unset( $dom );

		// halt if loading produces an error
		if ( ! $xml ) {
			return new WP_Error( 'SimpleXML_parse_error', __( 'There was an error when reading this XML file', 'formidable' ), libxml_get_errors() );
		}

        // add terms, forms (form and field ids), posts (post ids), and entries to db, in that order
        foreach ( array('term', 'form', 'view') as $item_type ) {
            // grab cats, tags, and terms, or forms or posts
            if ( isset($xml->{$item_type} ) ) {
                $function_name = 'import_xml_'. $item_type .'s';
                $imported = self::$function_name($xml->{$item_type}, $imported);
    		    unset($xml->{$item_type});
            }
        }

	    $return = apply_filters('frm_importing_xml', $imported, $xml );

	    return $return;
    }

    public static function import_xml_terms($terms, $imported) {
        foreach ( $terms as $t ) {
			if ( term_exists((string) $t->term_slug, (string) $t->term_taxonomy) ) {
			    continue;
			}

            $term_id = wp_insert_term( (string) $t->term_name, (string) $t->term_taxonomy, array(
                'slug'          => (string) $t->term_slug,
                'description'   => (string) $t->term_description,
                'term_parent'   => (string) $t->term_parent,
                'slug'          => (string) $t->term_slug,
            ));

            if ( $term_id ) {
                $imported['imported']['terms']++;
            }

            unset($term_id, $t);
		}

		return $imported;
    }

    public static function import_xml_forms($forms, $imported) {
		foreach ( $forms as $item ) {
            $form = array(
                'id'            => (int) $item->id,
                'form_key'      => (string) $item->form_key,
                'name'          => (string) $item->name,
                'description'   => (string) $item->description,
                'options'       => (string) $item->options,
                'logged_in'     => (int) $item->logged_in,
                'is_template'   => (int) $item->is_template,
                'default_template' => (int) $item->default_template,
                'editable'      => (int) $item->editable,
                'status'        => (string) $item->status,
                'parent_form_id' => isset($item->parent_form_id) ? (int) $item->parent_form_id : 0,
                'created_at'    => date('Y-m-d H:i:s', strtotime((string) $item->created_at)),
            );

            $form['options'] = FrmAppHelper::maybe_json_decode($form['options']);

            // if template, allow to edit if form keys match, otherwise, creation date must also match
            $edit_query = array('form_key' => $form['form_key'], 'is_template' => $form['is_template']);
            if ( ! $form['is_template'] ) {
                $edit_query['created_at'] = $form['created_at'];
            }

            if ( ! empty($form['parent_form_id']) && isset($imported['forms'][$form['parent_form_id']]) ) {
                // replace the old parent id with the new one
                $form['parent_form_id'] = $imported['forms'][$form['parent_form_id']];
            }

            $edit_query = apply_filters('frm_match_xml_form', $edit_query, $form);

            $this_form = FrmForm::getAll($edit_query, '', 1);
            unset($edit_query);

            if ( !empty($this_form) ) {
                $old_id = $form_id = $this_form->id;
                FrmForm::update($form_id, $form );
                $imported['updated']['forms']++;
                // Keep track of whether this specific form was updated or not
                $imported['form_status'][$form_id] = 'updated';

                $form_fields = FrmField::get_all_for_form($form_id);
                $old_fields = array();
                foreach ( $form_fields as $f ) {
                    $old_fields[$f->id] = $f;
                    $old_fields[$f->field_key] = $f->id;
                    unset($f);
                }
                $form_fields = $old_fields;
                unset($old_fields);
            } else {
                $old_id = false;
                //form does not exist, so create it
                if ( $form_id = FrmForm::create( $form ) ) {
                    $imported['imported']['forms']++;
                    // Keep track of whether this specific form was updated or not
                    $imported['form_status'][$form_id] = 'imported';
                }
            }

    		foreach ( $item->field as $field ) {
    		    $f = array(
    		        'id'            => (int) $field->id,
    		        'field_key'     => (string) $field->field_key,
    		        'name'          => (string) $field->name,
    		        'description'   => (string) $field->description,
    		        'type'          => (string) $field->type,
    		        'default_value' => FrmAppHelper::maybe_json_decode( (string) $field->default_value),
    		        'field_order'   => (int) $field->field_order,
    		        'form_id'       => (int) $form_id,
    		        'required'      => (int) $field->required,
    		        'options'       => FrmAppHelper::maybe_json_decode( (string) $field->options),
    		        'field_options' => FrmAppHelper::maybe_json_decode( (string) $field->field_options)
    		    );

    		    if ( is_array($f['default_value']) && in_array($f['type'], array(
    		        'text', 'email', 'url', 'textarea',
    		        'number','phone', 'date', 'time',
    		        'hidden', 'password', 'tag', 'image',
    		    )) ) {
    		        if ( count($f['default_value']) === 1 ) {
    		            $f['default_value'] = '['. reset($f['default_value']) .']';
    		        } else {
    		            $f['default_value'] = reset($f['default_value']);
    		        }
    		    }

    		    $f = apply_filters('frm_duplicated_field', $f);

    		    if ( ! empty($this_form) ) {
    		        // check for field to edit by field id
    		        if ( isset($form_fields[$f['id']]) ) {
    		            FrmField::update( $f['id'], $f );
    		            $imported['updated']['fields']++;

    		            unset($form_fields[$f['id']]);

    		            //unset old field key
    		            if ( isset($form_fields[$f['field_key']]) ) {
    		                unset($form_fields[$f['field_key']]);
    		            }
    		        } else if ( isset($form_fields[$f['field_key']]) ) {
    		            // check for field to edit by field key
    		            unset($f['id']);

    		            FrmField::update( $form_fields[$f['field_key']], $f );
    		            $imported['updated']['fields']++;

    		            unset($form_fields[$form_fields[$f['field_key']]]); //unset old field id
    		            unset($form_fields[$f['field_key']]); //unset old field key
    		        } else if ( FrmField::create( $f ) ) {
    		            // if no matching field id or key in this form, create the field
    		            $imported['imported']['fields']++;
    		        }
    		    } else if ( FrmField::create( $f ) ) {
		            $imported['imported']['fields']++;
    		    }

    		    unset($field);
    		}


    		// Delete any fields attached to this form that were not included in the template
    		if ( isset($form_fields) && !empty($form_fields) ) {
                foreach ($form_fields as $field){
                    if ( is_object($field) ) {
                        FrmField::destroy($field->id);
                    }
                    unset($field);
                }
                unset($form_fields);
            }

		    // Update field ids/keys to new ones
		    do_action('frm_after_duplicate_form', $form_id, $form, array('old_id' => $old_id));

            $imported['forms'][ (int) $item->id] = $form_id;

		    unset($form, $item);
		}

		return $imported;
    }

    public static function import_xml_views($views, $imported) {
        $imported['posts'] = array();
        $form_action_type = FrmFormActionsController::$action_post_type;

        $post_types = array(
            'frm_display' => 'views',
            $form_action_type => 'actions',
            'frm_styles'    => 'styles',
        );

        foreach ( $views as $item ) {
			$post = array(
				'post_title'    => (string) $item->title,
				'post_name'     => (string) $item->post_name,
				'post_type'     => (string) $item->post_type,
				'post_password' => (string) $item->post_password,
				'guid'          => (string) $item->guid,
				'post_status'   => (string) $item->status,
				'post_author'   => FrmAppHelper::get_user_id_param( (string) $item->post_author ),
				'post_id'       => (int) $item->post_id,
				'post_parent'   => (int) $item->post_parent,
				'menu_order'    => (int) $item->menu_order,
				'post_content'  => FrmFieldsHelper::switch_field_ids((string) $item->content),
				'post_excerpt'  => FrmFieldsHelper::switch_field_ids((string) $item->excerpt),
				'is_sticky'     => (string) $item->is_sticky,
				'comment_status' => (string) $item->comment_status,
				'post_date'     => (string) $item->post_date,
				'post_date_gmt' => (string) $item->post_date_gmt,
				'ping_status'   => (string) $item->ping_status,
                'postmeta'      => array(),
                'tax_input'     => array(),
			);

            $old_id = $post['post_id'];
            self::populate_post($post, $item, $imported);

			unset($item);

            if ( $post['post_type'] == $form_action_type ) {
                $action_control = FrmFormActionsController::get_form_actions( $post['post_excerpt'] );
                $post_id = $action_control->maybe_create_action( $post, $imported['form_status'] );
                unset($action_control);
            } else if ( $post['post_type'] == 'frm_styles' ) {
                // Properly encode post content before inserting the post
                $post['post_content'] = FrmAppHelper::maybe_json_decode( $post['post_content'] );
                $post['post_content'] = FrmAppHelper::prepare_and_encode( $post['post_content'] );

                // Create/update post now
                $post_id = wp_insert_post( $post );
            } else {
                // Create/update post now
                $post_id = wp_insert_post( $post );
            }

            if ( ! is_numeric($post_id) ) {
                continue;
            }

            self::update_postmeta($post, $post_id);

            $this_type = 'posts';
            if ( isset($post_types[$post['post_type']]) ) {
                $this_type = $post_types[$post['post_type']];
            }

            if ( isset($post['ID']) && $post_id == $post['ID'] ) {
                $imported['updated'][ $this_type ]++;
            } else {
                $imported['imported'][ $this_type ]++;
            }

            unset($post);

			$imported['posts'][ (int) $old_id] = $post_id;
		}

		return $imported;
    }

    private static function populate_post( &$post, $item, $imported ) {
		if ( isset($item->attachment_url) ) {
			$post['attachment_url'] = (string) $item->attachment_url;
		}

		if ( $post['post_type'] == FrmFormActionsController::$action_post_type ) {
		    // update to new form id
		    $post['menu_order'] = $imported['forms'][ (int) $post['menu_order'] ];
		}

		foreach ( $item->postmeta as $meta ) {
		    self::populate_postmeta($post, $meta, $imported);
			unset($meta);
		}

        self::populate_taxonomies($post, $item);

        self::maybe_editing_post($post);
    }

    private static function populate_postmeta( &$post, $meta, $imported ) {
        global $frm_duplicate_ids;

	    $m = array(
			'key'   => (string) $meta->meta_key,
			'value' => (string) $meta->meta_value
		);

		//switch old form and field ids to new ones
		if ( $m['key'] == 'frm_form_id' && isset($imported['forms'][ (int) $m['value'] ]) ) {
		    $m['value'] = $imported['forms'][ (int) $m['value'] ];
		} else {
		    $m['value'] = FrmAppHelper::maybe_json_decode($m['value']);

		    if ( ! empty($frm_duplicate_ids) ) {

		        if ( $m['key'] == 'frm_dyncontent' ) {
		            $m['value'] = FrmFieldsHelper::switch_field_ids($m['value']);
    		    } else if ( $m['key'] == 'frm_options' ) {

                    foreach ( array('date_field_id', 'edate_field_id') as $setting_name ) {
    		            if ( isset($m['value'][$setting_name]) && is_numeric($m['value'][$setting_name]) && isset($frm_duplicate_ids[$m['value'][$setting_name]]) ) {
    		                $m['value'][$setting_name] = $frm_duplicate_ids[$m['value'][$setting_name]];
    		            }
    		        }

                    $check_dup_array = array();
    		        if ( isset($m['value']['order_by']) && !empty($m['value']['order_by']) ) {
    		            if ( is_numeric($m['value']['order_by']) && isset($frm_duplicate_ids[$m['value']['order_by']]) ) {
    		                $m['value']['order_by'] = $frm_duplicate_ids[$m['value']['order_by']];
    		            } else if ( is_array($m['value']['order_by']) ) {
                            $check_dup_array[] = 'order_by';
    		            }
    		        }

    		        if ( isset($m['value']['where']) && !empty($m['value']['where']) ) {
    		            $check_dup_array[] = 'where';
    		        }

                    foreach ( $check_dup_array as $check_k ) {
                        foreach ( (array) $m['value'][$check_k] as $mk => $mv ) {
		                    if ( isset($frm_duplicate_ids[$mv]) ) {
		                        $m['value'][$check_k][$mk] = $frm_duplicate_ids[$mv];
		                    }
		                    unset($mk, $mv);
		                }
                    }
    		    }
		    }
		}

		if ( ! is_array($m['value']) ) {
		    $m['value'] = FrmAppHelper::maybe_json_decode($m['value']);
		}

		$post['postmeta'][ (string) $meta->meta_key ] = $m['value'];
    }

    /*
    * Add terms to post
    */
    private static function populate_taxonomies( &$post, $item ) {
		foreach ( $item->category as $c ) {
			$att = $c->attributes();
			if ( ! isset( $att['nicename'] ) ) {
                continue;
            }

		    $taxonomy = (string) $att['domain'];
		    if ( is_taxonomy_hierarchical($taxonomy) ) {
		        $name = (string) $att['nicename'];
		        $h_term = get_term_by('slug', $name, $taxonomy);
		        if ( $h_term ) {
		            $name = $h_term->term_id;
		        }
		        unset($h_term);
		    } else {
		        $name = (string) $c;
		    }

		    if ( ! isset($post['tax_input'][$taxonomy]) ) {
		        $post['tax_input'][$taxonomy] = array();
		    }

		    $post['tax_input'][$taxonomy][] = $name;
		    unset($name);
		}
    }

    /*
    * Edit post if the key and created time match
    */
    private static function maybe_editing_post( &$post ) {
		$match_by =  array(
		    'post_type'     => $post['post_type'],
		    'name'          => $post['post_name'],
		    'post_status'   => $post['post_status'],
		    'posts_per_page' => 1,
		);

		if ( in_array($post['post_status'], array('trash', 'draft')) ) {
		    $match_by['include'] = $post['post_id'];
		    unset($match_by['name']);
		}

		$editing = get_posts($match_by);

        if ( ! empty($editing) && current($editing)->post_date == $post['post_date'] ) {
            // set the id of the post to edit
            $post['ID'] = current($editing)->ID;
        }
    }

    private static function update_postmeta( &$post, $post_id ) {
        foreach ( $post['postmeta'] as $k => $v ) {
            if ( '_edit_last' == $k ) {
                $v = FrmAppHelper::get_user_id_param($v);
            } else if ( '_thumbnail_id' == $k && FrmAppHelper::pro_is_installed() ) {
                //change the attachment ID
                $v = FrmProXMLHelper::get_file_id($v);
            }

            update_post_meta($post_id, $k, $v);

            unset($k, $v);
        }
    }

    /**
     * @param string $message
     */
    public static function parse_message($result, &$message, &$errors) {
        if ( is_wp_error($result) ) {
            $errors[] = $result->get_error_message();
        } else if ( ! $result ) {
            return;
        }

        if ( ! is_array($result) ) {
            $message = $result;
            return;
        }

        $t_strings = array(
            'imported'  => __('Imported', 'formidable'),
            'updated'   => __('Updated', 'formidable'),
        );

        $message = '<ul>';
        foreach ( $result as $type => $results ) {
            if ( ! isset($t_strings[$type]) ) {
                // only print imported and updated
                continue;
            }

            $s_message = array();
            foreach ( $results as $k => $m ) {
                self::item_count_message($m, $k, $s_message);
                unset($k, $m);
            }

            if ( ! empty($s_message) ) {
                $message .= '<li><strong>'. $t_strings[$type] .':</strong> ';
                $message .= implode(', ', $s_message);
                $message .= '</li>';
            }

        }

        if ( $message == '<ul>' ) {
            $message = '';
            $errors[] = __('Nothing was imported or updated', 'formidable');
        } else {
            $message .= '</ul>';
        }
    }

    public static function item_count_message($m, $type, &$s_message) {
        if ( ! $m ) {
            return;
        }

        $strings = array(
            'forms'     => sprintf( _n( '%1$s Form', '%1$s Forms', $m, 'formidable' ), $m ),
            'fields'    => sprintf( _n( '%1$s Field', '%1$s Fields', $m, 'formidable' ), $m ),
            'items'     => sprintf( _n( '%1$s Entry', '%1$s Entries', $m, 'formidable' ), $m ),
            'views'     => sprintf( _n( '%1$s View', '%1$s Views', $m, 'formidable' ), $m ),
            'posts'     => sprintf( _n( '%1$s Post', '%1$s Posts', $m, 'formidable' ), $m ),
            'styles'     => sprintf( _n( '%1$s Style', '%1$s Styles', $m, 'formidable' ), $m ),
            'terms'     => sprintf( _n( '%1$s Term', '%1$s Terms', $m, 'formidable' ), $m ),
            'actions'   => sprintf( _n( '%1$s Form Action', '%1$s Form Actions', $m, 'formidable' ), $m ),
        );

        $s_message[] = isset($strings[$type]) ? $strings[$type] : ' '. $m .' '. ucfirst($type);
    }

	public static function cdata( $str ) {
	    $str = maybe_unserialize($str);
	    if ( is_array($str) ) {
	        $str = json_encode($str);
	    } else if (seems_utf8( $str ) == false ) {
			$str = utf8_encode( $str );
		}

        if ( is_numeric($str) ) {
            return $str;
        }

		// $str = ent2ncr(esc_html($str));
		$str = '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $str ) . ']]>';

		return $str;
	}

}
