## Installing

- Unzip and place the `XMLSitemaps` directory into your `/site/addons` folder.
- Done.

## Configuration

To configure XML Sitemaps, head to `Control Panel > Addons > XML Sitemaps > Settings`.

XML Sitemaps has sensible defaults set, but you'll still need to configure which Collections and Taxonomies you want Sitemaps for. By default, only the `Page` sitemap is enabled.

### URL Settings

The default URL formats will look familiar if you're migrating from a WordPress site that relied on Yoast for sitemaps, but you don't need to stick to them.

The following URLs are customizable:

- **Sitemap Index URL**
     
    No limits here, you can set it to anything you like.
    
   
- **Single Sitemap URL Format**

    You can change the format, but single Sitemap URLs must contain {alias} and {page} placeholders. The {alias} will be the name of the Sitemap, and {page} will be the page number, should your Sitemap exceed the max entries one Sitemap can hold (which is [configurable](#pagination-settings)).
    
    Aliases are better explained in the [Sitemaps section](#sitemap-settings).

The **Append Trailing Slash To URLs** setting dictates whether or not the URLs of items within a Sitemap have a trailing slash. It default to off.

### Pagination Settings

- **Max Entries Per Sitemap**
    
    Individual Sitemaps cannot contain more than 50,000 sitemaps, but it's common to limit Sitemaps to 1000 entries to keep them manageable. 
    
- **Use Page Number If Only One Page**

	The default behavior is to use "post-sitemap.xml" until there are two pages, at which point they become "post-sitemap1.xml" and "post-sitemap2.xml". Toggling this to true will always use "post-sitemap1.xml", even if there's no second page.
    
    
### Caching Settings

Generating the Sitemap each time it's requested would be absolutely mental - especially on larger sites. With the Caching settings, you can set how long generated Sitemaps stay cached, and when to clear those caches.

- **Sitemap Cache Length**

	The maximum amount of time a Sitemap can live in the cache before it's regenerated. Note that the whole Statamic file cache (including Sitemaps) is cleared whenever you save Addon settings through the CP.
    
- **Flush Cache On Content Save**

	Ah, selective cache purging. When true, XML Sitemaps will listen out for any Save actions, and clear out any Sitemaps associated with the piece of content that was saved.
    
 ### Sitemap Settings
 
 Every sitemap you add to the XML Sitemaps config can (and should) be given an alias. This alias will be used in place of the Collection or Taxonomy handle. The purpose behind Aliases are to minimize the amount of 301 redirections required when migrating to Statamic from another CMS.
 
>  If you're migrating from WordPress where you used Yoast, you might, for example, benefit from giving your "Articles" Collection an alias like "Post". The Sitemap URL then becomes `post-sitemap.xml` rather than `articles-sitemap.xml`.
 
 When you add any Sitemap to the config, you should provide an Alias for it. If you fail to do so, XML Sitemaps will fallback to the handle - but it can lead to some headaches if there any collisions.

Adding a Collection Sitemap is easy - click the `Add Collection Sitemap` button, pick a collection and assign it an alias. Done.

When adding a Taxonomy sitemap, you'll have to manually enter the Taxonomy handle. Statamic doesn't have a Taxonomy picker field available at the time of writing.

## FAQ

- **I added a Sitemap and it isn't showing, what's up?**
   
   Sitemaps need to contain some entries and have a valid route configured before they'll be generated.
  
- **A Taxonomy Sitemap's cache didn't clear when I updated an Entry that belongs to it?**

	Make sure your Taxonomy field is under the key `taxonomies`, otherwise no magic happens behind the scenes to link them. [See here](https://docs.statamic.com/fieldtypes/taxonomy) for more information.
