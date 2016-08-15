<?php $sub_section = empty($_REQUEST['subSection']) ? '' : $_REQUEST['subSection']; ?>
<div class="et-main-main clearfix inner-content" id="setting-language" <?php if ($sub_section != 'language') echo 'style="display:none"' ?> >
<?php 

	$pot	=	new PO();
	et_generate_pot ('ClassfiedEngine');
	$langArr	=	et_get_language_list (THEME_LANGUAGE_PATH);

	
	$genral_opts=	new CE_Options();
	$selected_lang	=	$genral_opts->get_language();
	
	
?>
	<div class="title font-quicksand"><?php _e("Website Language",ET_DOMAIN);?></div>
    <div id="setting_language_point" class="desc">
   		<?php _e("Select the language you want to use for your website.",ET_DOMAIN);?> 
   		<!-- <a class="find-out font-quicksand" href="#"><?php _e("Find out more",ET_DOMAIN);?> <span class="icon" data-icon="i"></span></a> -->
    	<ul class="list-language">
    	<?php foreach ($langArr as $value) { ?>
        	<li>
        		<a class="<?php if($selected_lang == $value) echo "active"?>" title="<?php echo $value?>" href="#et-change-language" rel="<?php echo  $value ?>"><?php echo $value?> </a>
        	</li>
        <?php }?>
        	<li class="new-language">
        		<button class="add-lang" id="add_language_point"><?php _e('Add a new language', ET_DOMAIN) ?><span class="icon" data-icon="+"></span></button>
        		<div class="lang-field-wrap">
        			<input id="new-language-name" type="text" placeholder="<?php _e("Enter language name", ET_DOMAIN) ?>" name="lang_name" class="input-new-lang">
        		</div>
        	</li>
        </ul>
        <div class="no-padding">	        				
			<div class="show-new-language">
				<div class="item form no-background no-padding no-margin">
					<div class="form-item form-item-short">
						<!-- <div class="label"><?php _e("Language name", ET_DOMAIN)?>:</div> -->
						<input id="new-language-name" class="bg-grey-input" type="text" placeholder="<?php _e("Enter the language's name", ET_DOMAIN)?>" />
						<button id="add-new-language" ><?php _e('Add language', ET_DOMAIN) ?><span class="icon" data-icon="+"></span></button>
						<a class="cancel" id="cancel-add-lang"><?php _e('Cancel', ET_DOMAIN) ?></a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<style type="text/css">
		#pager {
			font-family: arial;
			font-size: 10px;
			text-align: center;
			margin-top: 10px;
		}
		#pager a {
			border: 1px solid #999;
			color: #999;
			text-align: center;
			text-decoration: none;
			display: inline-block;
			width: 30px;
			height: 30px;
			margin: 0 2px;
			overflow: hidden;
		}
		#pager a span {
			line-height: 32px;
		}
		#pager a:hover {
			border-color: #666;
			color: #666;
		}
		#pager a.selected {
			border-color: #000;
			color: #000;
		}
		#pager a.hidden {
			display: none;
		}
		#pager a.ellipsis {
			border-color: transparent;
		}
		#pager a.ellipsis:after {
			content: '...';
		}
		#pager a.ellipsis span {
			display: none;
		}
		#setting-language textarea, .label {
			width: 800px !important;
		}
	</style>
	<div class="desc">   
		<div class="title font-quicksand"><?php _e("Translator",ET_DOMAIN);?></div>
        	<div class="item">
        		<div class="form no-background no-margin padding10">
        			<div class="form-item language-translate-bar">
		        		<div class="label"><?php _e("Translate a language",ET_DOMAIN);?></div>
		        		<div class="f-left-all width100p clearfix">
		        			<div  class="select-style et-button-select">
		        				<select id="base-language">
		        					<option class="empty" value=""><?php _e('Choose a Language', ET_DOMAIN) ?></option>
			        				<?php foreach ($langArr as $value) {?>
			        					<option value="<?php echo $value?>"><?php echo $value ?></option>
			        				<?php }?>
		        				</select>		
		        			</div>
		        			<div class="btn-language">
        						<button id="save-language"><?php _e('Save', ET_DOMAIN) ?> <span class="icon" data-icon="~"></span></button>
        					</div>
		        		</div>
	        		</div>
	        		
	        		<form id="language-list" style="height: 600px;overflow-y: scroll;">		        			
	        			
	        		</form>
	        		<div id="pager"></div>
				</div>
				
   			</div>
	</div>
</div>        			
