=== Image Carousel Module for Divi ===
Contributors: themeythemes
Tags: divi, image carousel, divi module, divi carousel
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.0
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin add an image carousel module to the Divi theme.

== Description ==

Add image carousels on your website with the Divi Image Carousel plugin. This plugin adds a new Image Carousel module in the Divi Builder. Once you activate the plugin a new module will appear in your module list by the name of Image Carousel. You can then use the module to add an Image Carousel anywhere inside the Divi Builder.

Transform your Divi website with the **Image Carousel Module for Divi**, the ultimate solution for creating stunning, responsive image carousels and sliders. Whether you're showcasing portfolios, products, team showcases, or galleries, this powerful Divi module gives you complete control over design, layout, and animation.

With seamless integration into the Divi Builder, the Image Carousel Module allows you to add visually appealing sliders anywhere on your website. Choose from multiple layouts, including Center Mode, Synced Slider, and Vertical Slider, and customize every aspect of your carousel to match your website’s style and functionality.

### Why Choose Image Carousel Module for Divi?

- **Fully Responsive:** Adjust slides count, scroll, arrows, and dots for desktop, tablet, and mobile.
- **Multiple Layouts:** Choose from Default, Center Mode, Synced Slider, or Vertical Slider layouts.
- **Customizable Navigation:** Show or hide arrows and dots, and style them with custom colors, backgrounds, and positions.
- **Advanced Animation Options:** Enable autoplay, infinite looping, and set animation speeds for smooth transitions.
- **SEO-Friendly:** Add alt and title attributes to images for better accessibility and search engine optimization.
- **Design Flexibility:** Customize borders, box shadows, and padding for images, center images, and main images.
- **Easy to Use:** No coding required—just drag, drop, and customize in the Divi Builder.

### Key Features:

- **Visual Builder Integration:** Add and edit carousels directly in the Divi Visual Builder.
- **Autoplay Animation:** Automatically scroll through images with adjustable animation speed.
- **Infinite Animation:** Enable seamless looping for a continuous carousel experience.
- **Center Mode Layout:** Highlight the center image with customizable padding and styling.
- **Synced Slider Layout:** Sync a thumbnail carousel with a larger image slider for better navigation.
- **Responsive Controls:** Adjust slides count, scroll, arrows, and dots for different devices.
- **Custom CSS Easing:** Choose from ease, linear, ease-in, ease-out, or ease-in-out for smooth animations.
- **Arrow and Dot Customization:** Style navigation elements with custom colors, hover effects, and positions.
- **Border and Box Shadow Styling:** Add borders and shadows to images for a polished look.

### How It Works:

1. **Activate the Plugin:** Once activated, the "Image Carousel" module will appear in your Divi Builder module list.
2. **Add the Module:** Drag and drop the Image Carousel module into any section of your Divi layout.
3. **Customize Your Carousel:** Select images, adjust layout settings, enable animations, and style navigation elements—all from the intuitive module settings panel.
4. **Publish and Enjoy:** Save your changes and watch your carousel come to life on your website.

### Demo & Tutorial:

- [Divi Image Carousel Demo](https://www.learnhowwp.com/divi-image-carousel-plugin/)  
- [How to Create an Image Carousel in Divi](https://www.learnhowwp.com/how-to-create-image-carousel-divi-free-plugin/)



**More Free Divi Plugins**

* [Divi Post Carousel Module](https://wordpress.org/plugins/post-carousel-divi/)
* [Divi Contact Form DB](https://wordpress.org/plugins/contact-form-db-divi/)
* [Divi Overlay on Images Module](https://wordpress.org/plugins/overlay-image-divi-module/)
* [Divi Menu Cart Module](https://wordpress.org/plugins/menu-cart-divi/)
* [Divi Flip Cards Module](https://wordpress.org/plugins/flip-cards-module-divi/)
* [Divi Breadcrumbs Module](https://wordpress.org/plugins/breadcrumbs-divi-module/)


If you have any questions or feature ideas please create a new thread in Support.

== Frequently Asked Questions ==
= Where can I access the module? =
 
After you activate the plugin a module should automatically appear in the module list. The name of the module is Image Carousel

= Where can I select the Images to show in the Carousel ? =
 
You can select the images that you want to show in the carousel at Module Settings > Content > Images.

= Does the carousel have autoplay animation or automatic animation option?=
 
You can enable autoplay for the carousel in Module Settings > Design > Carousel Animation > Autoplay. When you set it to Yes it will also enable the setting for animation speed.

= Does the carousel have infinite scroll or infinite animation option?=
 
You can enable infinite animation for the carousel in Module Settings > Design > Carousel Animation > Infinite Animation. 

= Change I hide the arrows and dots?=
 
The setting to show or hide the arrows and dots can be found under Module Settings > Content > Elements.

= Change I change the number of images in the carousel?=
 
You can change the number of images on each slide of carousel in Module Settings > Content > Elements > Slides Count.

= Change I change the number of images that scroll when arrow is clicked or on autoplay?=
 
You can change the number of images that scroll by changing the Slides Scroll setting at Module Settings > Content > Elements > Slides Scroll.

== Changelog ==

=1.0.1=
* Added support for configuring cssease (CSS easing options).
* Added rating notice.
* Fixed PHPCS errors using phpcbf and added missing doc comments for better code quality.
* Escaped output in lwp_hook_css function.
* Added nonce verification in lwp_get_carousel_images function.
* Removed default null value from $content parameter in render() method to fix warning.
* Replaced json_encode() with wp_json_encode() for safer JSON encoding in WordPress.

=1.0=
* Added option for speed animation.
* Added option for adaptive height.
* Fixed center padding working incorrectly in Visual Builder.
* Added title attribute on images.
* Fixed box shadow transition from last slide to first slide in center mode.

=0.9.6=
* Added option for border and box shadow for the center image in center mode
* Fixed center image not changing size in Visual Builder
* Removed text style options from module settings
* Added setting to change center padding in center mode
* Fixed issue with carousel not working in WordPress 5.6

=0.9.5=
* Added vertical slider option for default and centered sliders
* Fixed dots controls not working in the Visual Builder

=0.9.4=
* Switched to using Divi icon for arrows and dots from slick font
* Added arrow background option
* Added arrow position options
* Added hover controls on arrow color

=0.9.3=
* Added Synced Slider layout option
* Fixed jumping image issue in default slider
* Added custom links on the plugins page

=0.9.2=
* Fixed an issue where sometimes the slider will break when responsive controls are used on Slides Show and Slides Scroll
* Added Center Mode layout option
* Added styling option for Images

=0.9.1=
* Added responsive options to image count, slides scroll, arrows and dots controls.
* Added pause on hover option to the carousel
* Fixed description text for some fields in the module