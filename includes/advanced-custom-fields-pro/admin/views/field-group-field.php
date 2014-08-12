<?php 

global $post;


// get vars ($field)
extract( $args );


// vars
$field['prefix'] = "acf_fields[{$field['ID']}]";

$atts = array(
	'class' => "field field_type-{$field['type']}",
	'data-id'	=> $field['ID'],
	'data-key'	=> $field['key'],
	'data-type'	=> $field['type'],
);

$metas = array(
	'ID'			=> $field['ID'],
	'key'			=> $field['key'],
	'parent'		=> $field['parent'],
	'menu_order'	=> $field['menu_order'],
	'save'			=> '',
);

?>
<div <?php echo acf_esc_attr( $atts ); ?>>
	
	<div class="field-meta acf-hidden">
		<?php 
		
		// meta		
		foreach( $metas as $k => $v ) {
			
			acf_hidden_input(array( 'class' => "input-{$k}", 'name' => "{$field['prefix']}[{$k}]", 'value' => $v ));
				
		}
		
		?>
	</div>
	
	<ul class="field-info acf-hl acf-tbody">
		<li class="li-field_order">
			<span class="acf-icon large"><?php echo ($i + 1); ?></span>
			<pre class="pre-field_key"><?php echo $field['key']; ?></pre>
		</li>
		<li class="li-field_label">
			<strong>
				<a class="edit-field" title="<?php _e("Edit field",'acf'); ?>" href="#"><?php echo $field['label']; ?></a>
			</strong>
			<div class="row-options">
				<a class="edit-field" title="<?php _e("Edit field",'acf'); ?>" href="#"><?php _e("Edit",'acf'); ?></a>
				<a class="duplicate-field" title="<?php _e("Duplicate field",'acf'); ?>" href="#"><?php _e("Duplicate",'acf'); ?></a>
				<a class="move-field" title="<?php _e("Move field to another group",'acf'); ?>" href="#"><?php _e("Move",'acf'); ?></a>
				<a class="delete-field" title="<?php _e("Delete field",'acf'); ?>" href="#"><?php _e("Delete",'acf'); ?></a>
			</div>
		</li>
		<li class="li-field_name"><?php echo $field['name']; ?></li>
		<li class="li-field_type">
			<?php if( acf_field_type_exists($field['type']) ): ?>
				<?php echo acf_get_field_type_label($field['type']); ?>
			<?php else: ?>
				<b><?php _e('Error', 'acf'); ?></b> <?php _e('Field type does not exist', 'acf'); ?>
			<?php endif; ?>
		</li>	
	</ul>
	
	<div class="field-settings">			
		<table class="acf-table">
			<tbody>
				<?php 
		
				// label
				acf_render_field_wrap(array(
					'label'			=> __('Field Label','acf'),
					'instructions'	=> __('This is the name which will appear on the EDIT page','acf'),
					'required'		=> 1,
					'type'			=> 'text',
					'name'			=> 'label',
					'prefix'		=> $field['prefix'],
					'value'			=> $field['label'],
				), 'tr');
				
				
				// name
				acf_render_field_wrap(array(
					'label'			=> __('Field Name','acf'),
					'instructions'	=> __('Single word, no spaces. Underscores and dashes allowed','acf'),
					'required'		=> 1,
					'type'			=> 'text',
					'name'			=> 'name',
					'prefix'		=> $field['prefix'],
					'value'			=> $field['name'],
				), 'tr');
				
				
				// type
				acf_render_field_wrap(array(
					'label'			=> __('Field Type','acf'),
					'instructions'	=> '',
					'required'		=> 1,
					'type'			=> 'select',
					'name'			=> 'type',
					'prefix'		=> $field['prefix'],
					'value'			=> $field['type'],
					'choices' 		=> acf_get_field_types(),
				), 'tr');
				
				
				// instructions
				acf_render_field_wrap(array(
					'label'			=> __('Instructions','acf'),
					'instructions'	=> __('Instructions for authors. Shown when submitting data','acf'),
					'type'			=> 'textarea',
					'name'			=> 'instructions',
					'prefix'		=> $field['prefix'],
					'value'			=> $field['instructions'],
				), 'tr');
				
				
				// required
				acf_render_field_wrap(array(
					'label'			=> __('Required?','acf'),
					'instructions'	=> '',
					'type'			=> 'radio',
					'name'			=> 'required',
					'prefix'		=> $field['prefix'],
					'value'			=> $field['required'],
					'choices'		=> array(
						1				=> __("Yes",'acf'),
						0				=> __("No",'acf'),
					),
					'layout'		=> 'horizontal',
				), 'tr');
				
				
				// custom field options
				acf_render_field_settings( $field );
				
				
				// load view
				acf_get_view('field-group-field-conditional-logic', array( 'field' => $field ));
				
				?>
				<tr class="field_save">
					<td class="acf-label"></td>
					<td class="acf-input">
						<ul class="acf-hl acf-clearfix">
							<li>
								<a class="edit-field acf-button grey" title="<?php _e("Close Field",'acf'); ?>" href="#"><?php _e("Close Field",'acf'); ?></a>
							</li>
						</ul>
					</td>
				</tr>
			</tbody>
		</table>
	</div>	

</div>
