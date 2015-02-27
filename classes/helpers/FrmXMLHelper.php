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
		if ( !$success ) {
			return new WP_Error( 'SimpleXML_parse_error', __( 'There was an error when reading this XML file', 'formidable' ), libxml_get_errors() );
		}
		
		$xml = simplexml_import_dom( $dom );
		unset( $dom );

		// halt if loading produces an error
		if ( !$xml ) {
			return new WP_Error( 'SimpleXML_parse_error', __( 'There was an error when reading this XML file', 'formidable' ), libxml_get_errors() );
		}
        
        // add terms, forms (form and field ids), posts (post ids), and entries to db, in that order
        
        // grab cats, tags and terms
        if ( isset($xml->term) ) {
            $imported = self::import_xml_terms($xml->term, $imported);
		    unset($xml->term);
        }
		
		if ( isset($xml->form) ) {
            $imported = self::import_xml_forms($xml->form, $imported);
		    unset($xml->form);
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
            
            unset($term_id);
            unset($t);
		}
		
		return $imported;
    }
    
    public static function import_xml_forms($forms, $imported) {
        $frm_form = new FrmForm();
		$frm_field = new FrmField();
		
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
		        'created_at'    => date('Y-m-d H:i:s', strtotime((string) $item->created_at)),
		    );
		    
		    $form['options'] = FrmAppHelper::maybe_json_decode($form['options']);
		    
		    // if template, allow to edit if form keys match, otherwise, creation date must also match
		    $edit_query = array('form_key' => $form['form_key'], 'is_template' => $form['is_template']);
            if ( !$form['is_template'] ) {
                $edit_query['created_at'] = $form['created_at'];
            }
		    
		    $edit_query = apply_filters('frm_match_xml_form', $edit_query, $form);
		    
            $this_form = $frm_form->getAll($edit_query, '', 1);
            unset($edit_query);
            
            if ( !empty($this_form) ) {
                $form_id = $this_form->id;
                $frm_form->update($form_id, $form );
                $imported['updated']['forms']++;
                
                $form_fields = $frm_field->getAll(array('fi.form_id' => $form_id), 'field_order');
                $old_fields = array();
                foreach ( $form_fields as $f ) {
                    $old_fields[$f->id] = $f;
                    $old_fields[$f->field_key] = $f->id;
                    unset($f);
                }
                $form_fields = $old_fields;
                unset($old_fields);
            } else {
                //form does not exist, so create it
                if ( $form_id = $frm_form->create( $form ) ) {
                    $imported['imported']['forms']++;
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
    		    
    		    if ( is_array($f['default_value']) && in_array($f['type'], array('text', 'email', 'url', 'textarea', 'number', 'phone', 'date', 'time', 'image', 'hidden', 'password', 'tag')) ) {
    		        if ( count($f['default_value']) === 1 ) {
    		            $f['default_value'] = '['. reset($f['default_value']) .']';
    		        } else {
    		            $f['default_value'] = reset($f['default_value']);
    		        }
    		    }
    		    
    		    $f = apply_filters('frm_duplicated_field', $f);
    		    
    		    if ( $this_form ) {
    		        // check for field to edit by field id
    		        if ( isset($form_fields[$f['id']]) ) {
    		            $frm_field->update( $f['id'], $f );
    		            $imported['updated']['fields']++;
    		            
    		            unset($form_fields[$f['id']]);
    		            
    		            //unset old field key
    		            if ( isset($form_fields[$f['field_key']]) ) {
    		                unset($form_fields[$f['field_key']]);
    		            }
    		        } else if ( isset($form_fields[$f['field_key']]) ) {
    		            // check for field to edit by field key
    		            unset($f['id']);
    		            
    		            $frm_field->update( $form_fields[$f['field_key']], $f );
    		            $imported['updated']['fields']++;
    		            
    		            unset($form_fields[$form_fields[$f['field_key']]]); //unset old field id
    		            unset($form_fields[$f['field_key']]); //unset old field key
    		        } else if ( $frm_field->create( $f ) ) {
    		            // if no matching field id or key in this form, create the field
    		            $imported['imported']['fields']++;
    		        }
    		    } else if ( $frm_field->create( $f ) ) {
		            $imported['imported']['fields']++;
    		    }
    		    
    		    unset($field);
    		}
    		
    		
    		// Delete any fields attached to this form that were not included in the template
    		if ( isset($form_fields) && !empty($form_fields) ) {
                foreach ($form_fields as $field){
                    if ( is_object($field) ) {
                        $frm_field->destroy($field->id);
                    }
                    unset($field);
                }
                unset($form_fields);
            }
		    
		    
		    // Update field ids/keys to new ones
		    do_action('frm_after_duplicate_form', $form_id, $form);
            
            $imported['forms'][ (int) $item->id] = $form_id;
		    
		    unset($form);
		    unset($item);
		}
		
		unset($frm_form);
		unset($frm_field);
		
		return $imported;
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
