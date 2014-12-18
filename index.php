<?php


	require __DIR__.'/vendor/autoload.php';
	require __DIR__.'/vendor/phpQuery-0.9.5.386-onefile.php';


	use phpish\app;
	use phpish\template;
	use phpish\http;


	// TODO: Catch 404
	// TODO: Homepage with instructions


	app\get('/{app}', function ($req){

		if (!preg_match('/^[a-z0-9\-\_]*$/', $req['matches']['app'])) return app\response_500('Invalid app name');

		$app = $req['matches']['app'];
		$url = "https://apps.shopify.com/$app";

		if (isset($req['query']['page']) and ctype_digit($req['query']['page'])) $url .= "?page={$req['query']['page']}";

		$content = http\request("GET $url");
		$document = phpQuery::newDocumentHTML($content);

		$app = array();
		$app['page_count'] = (int) pq('.pagination a:last')->prev()->text();
		$app['current_page'] = (int) pq('.pagination em.current')->text();
		$app['review_count'] = (int) pq('a.resourcesratingssummary meta[itemprop="reviewCount"]', $review)->attr('content');
		$app['overall_rating'] = (int) pq('a.resourcesratingssummary meta[itemprop="ratingValue"]', $review)->attr('content');
		$app['best_rating'] = (int) pq('a.resourcesratingssummary meta[itemprop="bestRating"]', $review)->attr('content');
		$app['worst_rating'] = (int) pq('a.resourcesratingssummary meta[itemprop="worstRating"]', $review)->attr('content');
		//$app['prev'] = (int) pq('.pagination a[rel*="prev"]')->text();
		//$app['next'] = (int) pq('.pagination a[rel="next"]')->text();

		$reviews = array();
		foreach (pq('.resourcesreviews-reviews-star .contents') as $review)
		{
			$r = array();
			$r['shop']['myshopify_domain'] = substr(pq('a', $review)->attr('href'), 7);
			$r['shop']['name'] = pq('a', $review)->text();
			$r['published_at'] = pq('meta[itemprop="datePublished"]', $review)->attr('content');
			$r['rating'] = pq('meta[itemprop="reviewRating"]', $review)->attr('content');
			$r['content'] = pq('blockquote p', $review)->text();

			$reviews[] = $r;
		}

		$app['reviews'] = $reviews;

		return json_encode($app, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	});

?>