=== Classifiers ===
Contributors: taavi.aasver
Donate link: -
Tags: classify, classifiers, classifier, html, select, options, define, static, texts
Requires at least: -
Tested up to: 4.3.1
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

For theme developers. Instead of hard-coding certain texts (selectable options) to your template, make them manageable on the admin side.

== Description ==

This plugin is meant for theme developers.
It adds a new admin side page Classifiers, where you can define your classifiers and categories.

Example: Your site user needs to select her favorite color.
1.) Define classifier category 'color'
2.) Define classifiers: 'black', 'blue' etc.

Displaying these options in your template:
`$cm = new ClassifierManager();
print_r($cm->getClassifiers(1));`
OR AJAX:
`$http({
    method: 'POST',
    url: $scope.ajaxurl,
    params: {
        'action': 'wpc_ajax_getclassifiers',
        'security': $scope.nonce,
        'category' : 1
    }
}).success( function( data ) {
    //do something..
});`

== Installation ==

1. Upload `classifiers` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= What does this plugin do? =

This plugin adds a new admin side page Classifiers.
There you can define your classifiers and categories.

Example: Your site user needs to select her favorite color.
1.) Define classifier category 'color'
2.) Define classifiers: 'black', 'blue' etc.
3.) Display these options in your template:
`$cm = new ClassifierManager();
print_r($cm->getClassifiers(1));`
OR AJAX:
`$http({
    method: 'POST',
    url: $scope.ajaxurl,
    params: {
        'action': 'wpc_ajax_getclassifiers',
        'security': $scope.nonce,
        'category' : 1
    }
}).success( function( data ) {
    //do something..
});`

== Screenshots ==

1. Category list
2. Category view
3. Classifier view

== Changelog ==

= 1.1 [17/09/15] =

* New feature: Change classifier positions

= 1.0 =
* Initial

== Upgrade Notice ==

= 1.0 =
Initial