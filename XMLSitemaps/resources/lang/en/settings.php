<?php

return [

	// URLS

	'sitemap_index_url' => 'Sitemap Index URL',
	'sitemap_index_url_instruct' => 'The URL where you\'d like your Sitemap Index to appear.',

	'single_sitemap_url' => 'Single Sitemap URL Format',
	'single_sitemap_url_instruct' => 'The URL format for individual Sitemaps. Must contain the {alias} placeholder and a {page} placeholder (if you want your pagination to work). You can configure when {page} displays in the Pagination settings section.',

	'trailing_slash_urls' => 'Append Trailing Slash To URLs',
	'trailing_slash_urls_instruct' => 'Adds a trailing slash to entry URLs. If you enable this, be sure to put some sensible rewrite rules in your server config to avoid duplicate content.',


	// PAGINATION
	'max_entires_per_sitemap' => 'Max Entries Per Sitemap',
	'max_entires_per_sitemap_instruct' => 'This is the number of entries that\'ll cause the Sitemap to split into two pages.',

	'show_page_number_if_only_page' => 'Use Page Number If Only One Page',
	'show_page_number_if_only_page_instruct' => 'The default behavior is to use "post-sitemap.xml" until there are two pages, at which point they become "post-sitemap1.xml" and "post-sitemap2.xml". Toggling this to true will always use "post-sitemap1.xml", even if there\'s no second page.',


	// CACHING

	'cache_length' => 'Sitemap Cache Length',
	'cache_length_instruct' => 'Amount in minutes to hold generated Sitemaps in the cache.',

	'flush_cache_on_save' => 'Flush Cache On Content Save',
	'flush_cache_on_save_instruct' => 'Flushes any associated Sitemaps and the Sitemap Index from the cache when content is saved.',


	// SITEMAPS
	'sitemaps_instruct' => 'Add and manage Collection and Taxonomy Sitemaps and their respective aliases here. <br><br>Aliases are used in place of the handle in the URL and Sitemap name. If you do not specify an Alias, the handle of the item will be used.',

	'show_page_sitemap' => 'Show Page Sitemap',

	'page_sitemap_alias' => 'Page Sitemap Alias',
	'page_sitemap_alias_instruct' => 'Enter an alias for this Sitemap to be used instead of the handle. This will help you keep sitemaps in consistent locations if you\'re migrating from WordPress.',


	// Collection Sitemaps
	'collection_sitemaps' => 'Collection Sitemaps',
	'collection_sitemaps_instruct' => 'Collection Sitemaps will only be generated if the collection is not empty and has a route set.',

	// Taxonomy Sitemaps
	'taxonomy_sitemaps' => 'Taxonomy Sitemaps',
	'taxonomy_sitemaps_instruct' => 'Enter the taxonomy handle, which you can find in the URL when you edit a taxonomy. A taxonomy Sitemap contains entries that correspond to its terms (just like the category Sitemap that Yoast provides in WordPress).',


];