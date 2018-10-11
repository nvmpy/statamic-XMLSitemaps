{!! $xmlHeader !!}
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($sitemapIndex->getSitemaps() as $sitemap)
    <sitemap>
        <loc>{{ $sitemap->getLocation() }}</loc>
        <lastmod>{{ $sitemap->getLastModified()->format( 'Y-m-d\TH:i:sP' ) }}</lastmod>
    </sitemap>
@endforeach
</sitemapindex>