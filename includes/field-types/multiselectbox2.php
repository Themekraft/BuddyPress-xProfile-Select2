<?php

/**
 * Multi-selectbox xprofile field type.
 *
 * @since BuddyPress (2.0.0)
 */
class BP_XProfile_Field_Type_Multiselectbox2 extends BP_XProfile_Field_Type {

    /**
     * Constructor for the multi-selectbox field type
     *
     * @since BuddyPress (2.0.0)
     */
    public function __construct() {
        parent::__construct();

        $this->category = _x( 'Select2', 'xprofile field type category', 'buddypress' );
        $this->name     = _x( 'Multi Select Box', 'xprofile field type', 'buddypress' );

        $this->supports_multiple_defaults = true;
        $this->accepts_null_value         = true;
        $this->supports_options           = true;

        $this->set_format( '/^.+$/', 'replace' );

        /**
         * Fires inside __construct() method for BP_XProfile_Field_Type_Multiselectbox class.
         *
         * @since BuddyPress (2.0.0)
         *
         * @param BP_XProfile_Field_Type_Multiselectbox $this Current instance of
         *                                                    the field type multiple select box.
         */
        do_action( 'bp_xprofile_field_type_multiselectbox2', $this );
    }

    /**
     * Output the edit field HTML for this field type.
     *
     * Must be used inside the {@link bp_profile_fields()} template loop.
     *
     * @param array $raw_properties Optional key/value array of {@link http://dev.w3.org/html5/markup/select.html permitted attributes} that you want to add.
     * @since BuddyPress (2.0.0)
     */
    public function edit_field_html( array $raw_properties = array() ) {

        // user_id is a special optional parameter that we pass to
        // {@link bp_the_profile_field_options()}.
        if ( isset( $raw_properties['user_id'] ) ) {
            $user_id = (int) $raw_properties['user_id'];
            unset( $raw_properties['user_id'] );
        } else {
            $user_id = bp_displayed_user_id();
        }

        $r = bp_parse_args( $raw_properties, array(
            'multiple' => 'multiple',
            'id'       => bp_get_the_profile_field_input_name() . '[]',
            'name'     => bp_get_the_profile_field_input_name() . '[]',
        ) );

        $xprofile_select2_maximum_selection_size = bp_xprofile_get_meta( bp_get_the_profile_field_id(), 'field', 'xprofile_select2_maximum_selection_size' );

        ?>

        <label for="<?php bp_the_profile_field_input_name(); ?>[]"><?php bp_the_profile_field_name(); ?> <?php if ( bp_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'buddypress' ); ?><?php endif; ?></label>
        <script>
            jQuery(document).ready(function (){
                jQuery(".<?php echo bp_get_the_profile_field_input_name() ?>-select2").select2({
                    maximumSelectionSize: <?php echo $xprofile_select2_maximum_selection_size ?>
                });
            });
        </script>

        <?php

        /** This action is documented in bp-xprofile/bp-xprofile-classes */
        do_action( bp_get_the_profile_field_errors_action() ); ?>

        <select class="<?php echo bp_get_the_profile_field_input_name() ?>-select2 required-field" <?php echo $this->get_edit_field_html_elements( $r ); ?>>
            <?php bp_the_profile_field_options( array(
                'user_id' => $user_id
            ) ); ?>
        </select>

        <?php if ( ! bp_get_the_profile_field_is_required() ) : ?>

            <a class="clear-value" href="javascript:clear( '<?php echo esc_js( bp_get_the_profile_field_input_name() ); ?>[]' );">
                <?php esc_html_e( 'Clear', 'buddypress' ); ?>
            </a>

        <?php endif; ?>
    <?php
    }

    /**
     * Output the edit field options HTML for this field type.
     *
     * BuddyPress considers a field's "options" to be, for example, the items in a selectbox.
     * These are stored separately in the database, and their templating is handled separately.
     *
     * This templating is separate from {@link BP_XProfile_Field_Type::edit_field_html()} because
     * it's also used in the wp-admin screens when creating new fields, and for backwards compatibility.
     *
     * Must be used inside the {@link bp_profile_fields()} template loop.
     *
     * @param array $args Optional. The arguments passed to {@link bp_the_profile_field_options()}.
     * @since BuddyPress (2.0.0)
     */
    public function edit_field_options_html( array $args = array() ) {
        $original_option_values = maybe_unserialize( BP_XProfile_ProfileData::get_value_byid( $this->field_obj->id, $args['user_id'] ) );

        $options = $this->field_obj->get_children();
        $html    = '';

        if ( empty( $original_option_values ) && ! empty( $_POST['field_' . $this->field_obj->id] ) ) {
            $original_option_values = sanitize_text_field( $_POST['field_' . $this->field_obj->id] );
        }

        $option_values = (array) $original_option_values;
        for ( $k = 0, $count = count( $options ); $k < $count; ++$k ) {
            $selected = '';

            // Check for updated posted values, but errors preventing them from
            // being saved first time
            foreach( $option_values as $i => $option_value ) {
                if ( isset( $_POST['field_' . $this->field_obj->id] ) && $_POST['field_' . $this->field_obj->id][$i] != $option_value ) {
                    if ( ! empty( $_POST['field_' . $this->field_obj->id][$i] ) ) {
                        $option_values[] = sanitize_text_field( $_POST['field_' . $this->field_obj->id][$i] );
                    }
                }
            }

            // Run the allowed option name through the before_save filter, so
            // we'll be sure to get a match
            $allowed_options = xprofile_sanitize_data_value_before_save( $options[$k]->name, false, false );

            // First, check to see whether the user-entered value matches
            if ( in_array( $allowed_options, $option_values ) ) {
                $selected = ' selected="selected"';
            }

            // Then, if the user has not provided a value, check for defaults
            if ( ! is_array( $original_option_values ) && empty( $option_values ) && ! empty( $options[$k]->is_default_option ) ) {
                $selected = ' selected="selected"';
            }

            /**
             * Filters the HTML output for options in a multiselect input.
             *
             * @since BuddyPress (1.5.0)
             *
             * @param string $value    Option tag for current value being rendered.
             * @param object $value    Current option being rendered for.
             * @param int    $id       ID of the field object being rendered.
             * @param string $selected Current selected value.
             * @param string $k        Current index in the foreach loop.
             */
            $html .= apply_filters( 'bp_get_the_profile_field_options_multiselect', '<option' . $selected . ' value="' . esc_attr( stripslashes( $options[$k]->name ) ) . '">' . esc_html( stripslashes( $options[$k]->name ) ) . '</option>', $options[$k], $this->field_obj->id, $selected, $k );
        }

        echo $html;
    }

    /**
     * Output HTML for this field type on the wp-admin Profile Fields screen.
     *
     * Must be used inside the {@link bp_profile_fields()} template loop.
     *
     * @param array $raw_properties Optional key/value array of permitted attributes that you want to add.
     * @since BuddyPress (2.0.0)
     */
    public function admin_field_html( array $raw_properties = array() ) {
        $r = bp_parse_args( $raw_properties, array(
            'multiple' => 'multiple'
        ) ); ?>

        <select <?php echo $this->get_edit_field_html_elements( $r ); ?>>
            <?php bp_the_profile_field_options(); ?>
        </select>

    <?php
    }

    /**
     * Output HTML for this field type's children options on the wp-admin Profile Fields "Add Field" and "Edit Field" screens.
     *
     * Must be used inside the {@link bp_profile_fields()} template loop.
     *
     * @param BP_XProfile_Field $current_field The current profile field on the add/edit screen.
     * @param string $control_type Optional. HTML input type used to render the current field's child options.
     * @since BuddyPress (2.0.0)
     */
    public function admin_new_field_html( BP_XProfile_Field $current_field, $control_type = '' ) {
        $type = array_search( get_class( $this ), bp_xprofile_get_field_types() );
        if ( false === $type ) {
            return;
        }

        $class            = $current_field->type != $type ? 'display: none;' : '';
        $current_type_obj = bp_xprofile_create_field_type( $type );

        $xprofile_select2_maximum_selection_size = bp_xprofile_get_meta( $current_field->id, 'field', 'xprofile_select2_maximum_selection_size' );
        wp_nonce_field( 'xprofile-select2-edit-action', 'xprofile-select2-edit-action' );

        ?>

        <div id="<?php echo esc_attr( $type ); ?>" class="postbox bp-options-box" style="<?php echo esc_attr( $class ); ?> margin-top: 15px;">
            <h3><?php esc_html_e( 'Please enter options for this Field:', 'buddypress' ); ?></h3>
            <div class="inside">
                <p>
                    <label for="sort_order_<?php echo esc_attr( $type ); ?>"><?php esc_html_e( 'Sort Order:', 'buddypress' ); ?></label>
                    <select name="sort_order_<?php echo esc_attr( $type ); ?>" id="sort_order_<?php echo esc_attr( $type ); ?>" >
                        <option value="custom" <?php selected( 'custom', $current_field->order_by ); ?>><?php esc_html_e( 'Custom',     'buddypress' ); ?></option>
                        <option value="asc"    <?php selected( 'asc',    $current_field->order_by ); ?>><?php esc_html_e( 'Ascending',  'buddypress' ); ?></option>
                        <option value="desc"   <?php selected( 'desc',   $current_field->order_by ); ?>><?php esc_html_e( 'Descending', 'buddypress' ); ?></option>
                    </select>
                </p>

                <p>
                    <label for="sort_order_<?php echo esc_attr( $type ); ?>"><?php esc_html_e( 'Sort Order:', 'buddypress' ); ?></label>
                    <input type='text' name='xprofile-select2-maximum-selection-size' id='xprofile-select2-maximum-selection-size' class='xprofile-select2-text' value ='<?php echo $xprofile_select2_maximum_selection_size ?>' />
                </p>

                <?php

                // Does option have children?
                $options = $current_field->get_children( true );

                // If no children options exists for this field, check in $_POST
                // for a submitted form (e.g. on the "new field" screen).
                if ( empty( $options ) ) {

                    $options = array();
                    $i       = 1;

                    while ( isset( $_POST[$type . '_option'][$i] ) ) {

                        // Multiselectbox and checkboxes support MULTIPLE default options; all other core types support only ONE.
                        if ( $current_type_obj->supports_options && ! $current_type_obj->supports_multiple_defaults && isset( $_POST["isDefault_{$type}_option"][$i] ) && (int) $_POST["isDefault_{$type}_option"] === $i ) {
                            $is_default_option = true;
                        } elseif ( isset( $_POST["isDefault_{$type}_option"][$i] ) ) {
                            $is_default_option = (bool) $_POST["isDefault_{$type}_option"][$i];
                        } else {
                            $is_default_option = false;
                        }

                        // Grab the values from $_POST to use as the form's options
                        $options[] = (object) array(
                            'id'                => -1,
                            'is_default_option' => $is_default_option,
                            'name'              => sanitize_text_field( stripslashes( $_POST[$type . '_option'][$i] ) ),
                        );

                        ++$i;
                    }

                    // If there are still no children options set, this must be the "new field" screen, so add one new/empty option.
                    if ( empty( $options ) ) {
                        $options[] = (object) array(
                            'id'                => -1,
                            'is_default_option' => false,
                            'name'              => '',
                        );
                    }
                }

                // Render the markup for the children options
                if ( ! empty( $options ) ) {
                    $default_name = '';

                    for ( $i = 0, $count = count( $options ); $i < $count; ++$i ) :
                        $j = $i + 1;

                        // Multiselectbox and checkboxes support MULTIPLE default options; all other core types support only ONE.
                        if ( $current_type_obj->supports_options && $current_type_obj->supports_multiple_defaults ) {
                            $default_name = '[' . $j . ']';
                        }
                        ?>

                        <div id="<?php echo esc_attr( "{$type}_div{$j}" ); ?>" class="bp-option sortable">
                            <span class="bp-option-icon grabber"></span>
                            <input type="text" name="<?php echo esc_attr( "{$type}_option[{$j}]" ); ?>" id="<?php echo esc_attr( "{$type}_option{$j}" ); ?>" value="<?php echo esc_attr( stripslashes( $options[$i]->name ) ); ?>" />
                            <label>
                                <input type="<?php echo esc_attr( $control_type ); ?>" name="<?php echo esc_attr( "isDefault_{$type}_option{$default_name}" ); ?>" <?php checked( $options[$i]->is_default_option, true ); ?> value="<?php echo esc_attr( $j ); ?>" />
                                <?php _e( 'Default Value', 'buddypress' ); ?>
                            </label>

                            <?php if ( 1 !== $j ) : ?>
                                <div class ="delete-button">
                                    <a href='javascript:hide("<?php echo esc_attr( "{$type}_div{$j}" ); ?>")' class="delete"><?php esc_html_e( 'Delete', 'buddypress' ); ?></a>
                                </div>
                            <?php endif; ?>

                        </div>

                    <?php endfor; ?>

                    <input type="hidden" name="<?php echo esc_attr( "{$type}_option_number" ); ?>" id="<?php echo esc_attr( "{$type}_option_number" ); ?>" value="<?php echo esc_attr( $j + 1 ); ?>" />
                <?php } ?>

                <div id="<?php echo esc_attr( "{$type}_more" ); ?>"></div>
                <p><a href="javascript:add_option('<?php echo esc_js( $type ); ?>')"><?php esc_html_e( 'Add Another Option', 'buddypress' ); ?></a></p>
            </div>
        </div>

    <?php
    }
}

add_action( 'xprofile_field_after_save', 'tk_xprofile_save_select2' );
function tk_xprofile_save_select2( $field ) {

    if( isset( $_POST['xprofile-select2-maximum-selection-size'] ) ) {

        if( !wp_verify_nonce( $_POST['xprofile-select2-edit-action'], 'xprofile-select2-edit-action' ) )
            return;

        bp_xprofile_update_field_meta( $field->id, 'xprofile_select2_maximum_selection_size', $_POST['xprofile-select2-maximum-selection-size'] );

    }

}