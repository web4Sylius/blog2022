/**
  * SAB
  * (c) WebFactory Ltd, 2016 - 2021
  */

wp.SAB = 'undefined' === typeof( wp.SAB ) ? {} : wp.SAB;
wp.SAB.views = 'undefined' === typeof( wp.SAB.views ) ? {} : wp.SAB.views;
wp.SAB.models = 'undefined' === typeof( wp.SAB.models ) ? {} : wp.SAB.models;
wp.SAB.contructors = 'undefined' === typeof( wp.SAB.contructors ) ? [] : wp.SAB.contructors;

wp.SAB.models.Settings = Backbone.Model.extend({
	initialize: function(){
        var model = this;

  		var view = new wp.SAB.views.Settings({
  			model: this,
  			el: jQuery( '#sabox-container' )
  		});

  		this.set( 'view', view );
    },
    getAttribute: function( type ){
    	var value = this.get( type );

    	if ( 'undefined' == typeof value ) {
    		value = jQuery( '#' + type ).val();
    	}

    	return value;
    }
});

wp.SAB.views.Settings = Backbone.View.extend({

	events: {
		// Settings specific events
        'keyup input':         'updateModel',
        'keyup textarea':      'updateModel',
        'change input':        'updateModel',
        'change textarea':     'updateModel',
        'blur textarea':       'updateModel',
        'change select':       'updateModel',
    },

    initialize: function( args ) {

    	// Check for Google Fonts
    	this.checkBunnyFonts();

    	this.listenTo( this.model, 'change:sab_email', this.emailVisibility );

    	// Author website
    	this.listenTo( this.model, 'change:sab_web', this.websiteVisibility );
    	this.listenTo( this.model, 'change:sab_web_position', this.websitePosition );

    	// Social Icons
    	this.listenTo( this.model, 'change:sab_hide_socials', this.socialsVisibility );
    	this.listenTo( this.model, 'change:sab_colored', this.socialIconTypeVisibility );
    	this.listenTo( this.model, 'change:sab_icons_style', this.socialIconTypeVisibility );
    	this.listenTo( this.model, 'change:sab_social_hover', this.socialIconHover );
    	this.listenTo( this.model, 'change:sab_box_long_shadow', this.socialIconShadow );
    	this.listenTo( this.model, 'change:sab_box_thin_border', this.socialIconBorder );

    	// Avatar
    	this.listenTo( this.model, 'change:sab_avatar_style', this.avatarStyle );
    	this.listenTo( this.model, 'change:sab_avatar_size', this.avatarSize );
    	this.listenTo( this.model, 'change:sab_avatar_hover', this.avatarHover );

    	// Padding
    	this.listenTo( this.model, 'change:sab_box_padding_top_bottom', this.adjustPadding );
    	this.listenTo( this.model, 'change:sab_box_padding_left_right', this.adjustPadding );

    	// Author Box Background
    	this.listenTo( this.model, 'change:sab_bg_image', this.adjustBoxBackground );

        // Author Box Border
    	this.listenTo( this.model, 'change:sab_box_border_width', this.adjustBorder );
    	this.listenTo( this.model, 'change:sab_box_border', this.adjustBorder );
        this.listenTo( this.model, 'change:sab_border_type', this.adjustBorder );

    	// Adjust Author name settings
    	this.listenTo( this.model, 'change:sab_box_name_size', this.adjustAuthorName );
    	this.listenTo( this.model, 'change:sab_box_name_font', this.adjustAuthorName );
    	this.listenTo( this.model, 'change:sab_box_author_color', this.adjustAuthorName );

    	// Adjust Author website settings
    	this.listenTo( this.model, 'change:sab_box_web_font', this.adjustAuthorWebsite );
    	this.listenTo( this.model, 'change:sab_box_web_size', this.adjustAuthorWebsite );
    	this.listenTo( this.model, 'change:sab_box_web_color', this.adjustAuthorWebsite );

    	// Adjust Author description settings
    	this.listenTo( this.model, 'change:sab_box_desc_font', this.adjustAuthorDescription );
    	this.listenTo( this.model, 'change:sab_box_desc_size', this.adjustAuthorDescription );
    	this.listenTo( this.model, 'change:sab_box_author_p_color', this.adjustAuthorDescription );
    	this.listenTo( this.model, 'change:sab_box_author_a_color', this.adjustAuthorDescription );
    	this.listenTo( this.model, 'change:sab_desc_style', this.adjustAuthorDescription );

        //Tabs
    	this.listenTo( this.model, 'change:sab_tab_background_color', this.adjustTabs );
        this.listenTo( this.model, 'change:sab_tab_text_color', this.adjustTabs );
        this.listenTo( this.model, 'change:sab_tab_border_color', this.adjustTabs );
        this.listenTo( this.model, 'change:sab_tab_hover_background_color', this.adjustTabs );
        this.listenTo( this.model, 'change:sab_tab_hover_text_color', this.adjustTabs );
        this.listenTo( this.model, 'change:sab_tab_hover_border_color', this.adjustTabs );

    	// Icon Size
    	this.listenTo( this.model, 'change:sab_box_icon_size', this.adjustIconSize );

    	// Social Bar Background Color
    	this.listenTo( this.model, 'change:sab_box_icons_back', this.changeSocialBarBackground );

    	// Author Box Background Color
    	this.listenTo( this.model, 'change:sab_box_author_back', this.changeAuthorBoxBackground );

    	// Author Box Background Color
    	this.listenTo( this.model, 'change:sab_box_icons_color', this.changeSocialIconsColor );

    },

    emailVisibility: function() {
    	var showEmail = wp.SAB.Settings.get( 'sab_email' );

    	if ( '1' == showEmail ) {
    		jQuery('.sab-user_email').parent().show();
    	}else{
    		jQuery('.sab-user_email').parent().hide();
    	}
    },

    socialsVisibility: function(){
    	var hideSocials = wp.SAB.Settings.get( 'sab_hide_socials' );

    	if ( '1' == hideSocials ) {
    		jQuery('.saboxplugin-socials').hide();
    	}else{
    		jQuery('.saboxplugin-socials').show();
    	}
    },

    websiteVisibility: function(){
    	var showWesite = wp.SAB.Settings.get( 'sab_web' );

    	if ( '1' == showWesite ) {
    		jQuery('.saboxplugin-web').show();
    	}else{
    		jQuery('.saboxplugin-web').hide();
    	}
    },

    websitePosition: function() {
    	var attribute = wp.SAB.Settings.get( 'sab_web_position' );

    	if ( '1' == attribute ) {
    		jQuery('.saboxplugin-web').addClass( 'sab-web-position' );
    	}else{
    		jQuery('.saboxplugin-web').removeClass( 'sab-web-position' );
    	}
    },

    socialIconTypeVisibility: function() {
    	var iconType = wp.SAB.Settings.getAttribute( 'sab_colored' ),
    		iconStyle = wp.SAB.Settings.getAttribute( 'sab_icons_style' );

    	jQuery('.saboxplugin-socials').removeClass( 'sab-show-simple sab-show-circle sab-show-square' );
    	if ( '1' == iconType ) {
    		if ( '1' == iconStyle ) {
    			jQuery('.saboxplugin-socials').addClass( 'sab-show-circle' );
    		}else{
    			jQuery('.saboxplugin-socials').addClass( 'sab-show-square' );
    		}
    	}else{
    		jQuery('.saboxplugin-socials').addClass( 'sab-show-simple' );
    	}

    },

    socialIconHover: function() {
    	var attribute = wp.SAB.Settings.get( 'sab_social_hover' );

    	if ( '1' == attribute ) {
    		jQuery('.saboxplugin-socials').addClass( 'sab-rotate-icons' );
    	}else{
    		jQuery('.saboxplugin-socials').removeClass( 'sab-rotate-icons' );
    	}
    },

    socialIconShadow: function() {
    	var attribute = wp.SAB.Settings.get( 'sab_box_long_shadow' );

    	if ( '1' == attribute ) {
    		jQuery('.saboxplugin-socials').removeClass( 'without-long-shadow' );
    	}else{
    		jQuery('.saboxplugin-socials').addClass( 'without-long-shadow' );
    	}
    },

    socialIconBorder: function() {
    	var attribute = wp.SAB.Settings.get( 'sab_box_thin_border' );

    	if ( '1' == attribute ) {
    		jQuery('.saboxplugin-socials').addClass( 'sab-icons-with-border' );
    	}else{
    		jQuery('.saboxplugin-socials').removeClass( 'sab-icons-with-border' );
    	}
    },

    avatarSize: function() {
    	var size = wp.SAB.Settings.get( 'sab_avatar_size' );
        jQuery('.saboxplugin-gravatar').children('img').css('max-width', size);
        jQuery('.saboxplugin-gravatar').children('img').attr('width', size);
        jQuery('.saboxplugin-gravatar').children('img').attr('height', size);
    },

    avatarStyle: function() {
    	var attribute = wp.SAB.Settings.get( 'sab_avatar_style' );

        jQuery('.saboxplugin-gravatar').removeClass( 'sab-round-image' );
        jQuery('.saboxplugin-gravatar').removeClass( 'sab-fancy-image' );
        jQuery('.saboxplugin-gravatar').removeClass( 'sab-elypse-image' );
        jQuery('.saboxplugin-gravatar').removeClass( 'sab-shear-image' );
        jQuery('.saboxplugin-gravatar').removeClass( 'sab-speed-image' );

    	if ( '1' == attribute ) {
    		jQuery('.saboxplugin-gravatar').addClass( 'sab-round-image' );
    	} else if ( '2' == attribute ) {
    		jQuery('.saboxplugin-gravatar').addClass( 'sab-fancy-image' );
    	} else if ( '3' == attribute ) {
    		jQuery('.saboxplugin-gravatar').addClass( 'sab-elypse-image' );
    	} else if ( '4' == attribute ) {
    		jQuery('.saboxplugin-gravatar').addClass( 'sab-shear-image' );
    	} else if ( '5' == attribute ) {
    		jQuery('.saboxplugin-gravatar').addClass( 'sab-speed-image' );
    	}
    },

    avatarHover: function() {
    	var attribute = wp.SAB.Settings.get( 'sab_avatar_hover' );

    	if ( '1' == attribute ) {
    		jQuery('.saboxplugin-gravatar').addClass( 'sab-rotate-img' );
    	}else{
    		jQuery('.saboxplugin-gravatar').removeClass( 'sab-rotate-img' );
    	}
    },

    adjustPadding: function() {
    	var paddingTopBottom = wp.SAB.Settings.getAttribute( 'sab_box_padding_top_bottom' ),
    		paddingLeftRight = wp.SAB.Settings.getAttribute( 'sab_box_padding_left_right' );

    	jQuery( '.saboxplugin-wrap' ).css({ 'padding' : paddingTopBottom + ' ' + paddingLeftRight });

    },

    adjustBoxBackground: function() {
    	var background_url = wp.SAB.Settings.getAttribute( 'sab_bg_image' );
    	if ( '' != background_url ) {
            jQuery( '.saboxplugin-wrap' ).css({ 'background-image' : 'url(\'' + background_url + '\')'});
            jQuery( '#sab_bg_image').parents('.sab-element-image-wrapper').find('.sabox-preview-area').html('<img src="' + background_url + '" />&nbsp;<a href="javascript: void(0);" class="sab-remove-image">Remove Image</a>');
    	} else {
            jQuery( '.saboxplugin-wrap' ).css({ 'background-image' : 'none'});
            jQuery( '#sab_bg_image').parents('.sab-element-image-wrapper').find('.sabox-preview-area').html('<span class="sabox-preview-area-placeholder">Select an image from our 400,000+ images gallery, or upload your own</span>');
        }
    },

    adjustBorder: function() {
    	var border = wp.SAB.Settings.getAttribute( 'sab_box_border_width' ),
    		borderColor = wp.SAB.Settings.getAttribute( 'sab_box_border' ),
            borderStyle = wp.SAB.Settings.getAttribute( 'sab_border_type' );

        if ( '' == borderStyle ) {
            borderStyle = 'solid';
        }
        jQuery( '.saboxplugin-wrap' ).css({ 'border-width' : border });
        jQuery( '.saboxplugin-wrap' ).css({ 'border-style' : borderStyle });

    	if ( '' != borderColor ) {
    		jQuery( '.saboxplugin-wrap' ).css({'border-color' : borderColor });
    	}
    },

    adjustAuthorName: function() {
    	var font = wp.SAB.Settings.getAttribute( 'sab_box_name_font' ),
    		size = wp.SAB.Settings.getAttribute( 'sab_box_name_size' ),
    		color = wp.SAB.Settings.getAttribute( 'sab_box_author_color' ),
    		lineHeight = parseInt( size ) + 7;

    	if ( '' == color ) {
    		color = 'inherit';
    	}

    	if ( '' == font || 'None' == font ) {
    		font = 'inherit';
    	}else{
    		this.loadBunnyFonts( font );
    	}


        jQuery( '.saboxplugin-wrap .saboxplugin-authorname a' ).css({ 'font-family' : font, 'color' : color, 'font-size': size, 'line-height' : lineHeight.toString() + 'px' });
        jQuery( '.saboxplugin-wrap .saboxplugin-authorname span' ).css({ 'color' : color});
    },

    adjustTabs: function() {
    	var tab_bg_color = wp.SAB.Settings.getAttribute( 'sab_tab_background_color' ),
    		tab_text_color = wp.SAB.Settings.getAttribute( 'sab_tab_text_color' ),
            tab_border_color = wp.SAB.Settings.getAttribute( 'sab_tab_border_color' ),
            tab_hover_bg_color = wp.SAB.Settings.getAttribute( 'sab_tab_hover_background_color' ),
    		tab_hover_text_color = wp.SAB.Settings.getAttribute( 'sab_tab_hover_text_color' ),
            tab_hover_border_color = wp.SAB.Settings.getAttribute( 'sab_tab_hover_border_color' );

    	if ( '' == tab_bg_color ) {
            tab_bg_color = 'inherit';
        }

        if ( '' == tab_text_color ) {
    		tab_text_color = 'inherit';
        }

        if ( '' == tab_border_color ) {
    		tab_border_color = 'inherit';
        }

        if ( '' == tab_hover_bg_color ) {
    		tab_hover_bg_color = 'inherit';
        }

        if ( '' == tab_hover_text_color ) {
    		tab_hover_text_color = 'inherit';
        }

        if ( '' == tab_hover_border_color ) {
    		tab_hover_border_color = 'inherit';
    	}

        jQuery( '.saboxplugin-wrap .saboxplugin-tabs-wrapper li' ).css({ 'color' : tab_text_color, 'background-color' : tab_bg_color, 'border-color': tab_border_color });
        var hover_style = '.saboxplugin-wrap .saboxplugin-tabs-wrapper li:hover, .saboxplugin-wrap .saboxplugin-tabs-wrapper li:active{color: '+tab_hover_text_color+' !important;background-color: '+tab_hover_bg_color+' !important;border-color: '+tab_hover_border_color+' !important;}';
        if(jQuery('#sab_tabs_css').length > 0){
            jQuery('#sab_tabs_css').html(hover_style);
        } else {
            jQuery('body').append('<style id="sab_tabs_css">' + hover_style + '</style>')
        }
    },

    adjustAuthorWebsite: function() {
    	var font = wp.SAB.Settings.getAttribute( 'sab_box_web_font' ),
    		size = wp.SAB.Settings.getAttribute( 'sab_box_web_size' ),
    		color = wp.SAB.Settings.getAttribute( 'sab_box_web_color' ),
    		lineHeight = parseInt( size ) + 7;

    	if ( '' == color ) {
    		color = 'inherit';
    	}

    	if ( '' == font || 'None' == font ) {
    		font = 'inherit';
    	}else{
    		this.loadBunnyFonts( font );
    	}


    	jQuery( '.saboxplugin-wrap .saboxplugin-web a' ).css({ 'font-family' : font, 'color' : color, 'font-size': size, 'line-height' : lineHeight.toString() + 'px' });
    },

    adjustAuthorDescription: function() {
    	var font = wp.SAB.Settings.getAttribute( 'sab_box_desc_font' ),
    		size = wp.SAB.Settings.getAttribute( 'sab_box_desc_size' ),
    		color = wp.SAB.Settings.getAttribute( 'sab_box_author_p_color' ),
    		link_color = wp.SAB.Settings.getAttribute( 'sab_box_author_a_color' ),
    		style = wp.SAB.Settings.getAttribute( 'sab_desc_style' ),
    		lineHeight = parseInt( size ) + 7;

    	if ( '' == color ) {
    		color = 'inherit';
    	}

    	if ( '' == link_color ) {
    		link_color = 'inherit';
    	}

    	if ( '' == font || 'None' == font ) {
    		font = 'inherit';
    	}else{
    		this.loadBunnyFonts( font );
    	}

    	if ( '0' == style ) {
    		style = 'normal';
    	}else{
    		style = 'italic';
    	}


    	jQuery( '.saboxplugin-wrap .saboxplugin-desc p, .saboxplugin-wrap .saboxplugin-desc' ).css({ 'font-family' : font, 'color' : color, 'font-size': size, 'line-height' : lineHeight.toString() + 'px', 'font-style' : style });
    	jQuery( '.saboxplugin-wrap .saboxplugin-desc a' ).css({ 'font-family' : font, 'color' : link_color, 'font-size': size, 'line-height' : lineHeight.toString() + 'px', 'font-style' : style });
    },

    adjustIconSize: function() {
    	var size = this.model.get( 'sab_box_icon_size' ),
    		size2x = parseInt( size ) * 2;

    	jQuery( '.saboxplugin-wrap .saboxplugin-socials a.saboxplugin-icon-grey svg' ).css({ 'width' : size, 'height' : size });
    	jQuery( '.saboxplugin-wrap .saboxplugin-socials a.saboxplugin-icon-color svg' ).css({ 'width' : size2x.toString() + 'px', 'height' : size2x.toString() + 'px' });
    },

    changeSocialBarBackground: function() {
    	var color = this.model.get( 'sab_box_icons_back' );

    	if ( '' == color ) {
    		color = 'inherit';
    	}

    	jQuery( '.saboxplugin-wrap .saboxplugin-socials' ).css({ 'background-color' : color });

    },

    changeAuthorBoxBackground: function() {
    	var color = this.model.get( 'sab_box_author_back' );

    	if ( '' == color ) {
    		color = 'inherit';
    	}

    	jQuery( '.saboxplugin-wrap' ).css({ 'background-color' : color });

    },

    changeSocialIconsColor: function() {
    	var color = this.model.get( 'sab_box_icons_color' );

    	if ( '' == color ) {
    		color = 'inherit';
    	}

    	jQuery( '.saboxplugin-wrap .saboxplugin-socials a.saboxplugin-icon-grey svg' ).css({ 'color' : color, 'fill' : color });

    },

    updateModel: function( event ) {
    	var value, setting;


    	// Check if the target has a data-field. If not, it's not a model value we want to store
        if ( undefined === event.target.id ) {
            return;
        }

        // Update the model's value, depending on the input type
        if ( event.target.type == 'checkbox' ) {
            value = ( event.target.checked ? '1' : '0' );
        } else {
            value = event.target.value;
        }

        // Update the model
        this.model.set( event.target.id, value );

    },

    checkBunnyFonts: function() {
    	var authorFont = this.model.getAttribute( 'sab_box_name_font' ),
    		webFont = this.model.getAttribute( 'sab_box_web_font' ),
    		descriptionFont = this.model.getAttribute( 'sab_box_desc_font' );

    	if (  '' != authorFont && 'None' != authorFont  ) {
    		this.loadBunnyFonts( authorFont );
    	}

    	if (  '' != webFont && 'None' != webFont  ) {
    		this.loadBunnyFonts( webFont );
    	}

    	if (  '' != descriptionFont && 'None' != descriptionFont  ) {
    		this.loadBunnyFonts( descriptionFont );
    	}

    },

    loadBunnyFonts: function( font ) {
    	if ( ! wp.SAB.Fonts.includes( font ) ) {
    		wp.SAB.Fonts.push( font );
    		WebFont.load({
			    bunny: {
			      families: [ font ]
			    }
			});
    	}
    },

});

jQuery( document ).ready(function(){

	wp.SAB.Fonts = [];
	wp.SAB.Settings = new wp.SAB.models.Settings();

});
