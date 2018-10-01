<?php
/**
 * Youtube playlists viewer
 * 
 * This demo might be useful if you want to show playlists and videos from any Youtube channel on your website
 * The script uses XML files to cache responses from youtube API
 * 
 * To compose page data and implement MVC pattern script uses XML+XSLT
 * More info about XML library see at https://github.com/pgooood/php-xml-lib
 */

/* 
 * First you have to get your API key at https://developers.google.com/youtube/v3/getting-started
 */
$apiKey = '';
/* 
 * Youtube channel id
 * you can obtain it from the channel URL, for example:
 * https://www.youtube.com/channel/UClIIy-aQBXRi1OHupBcrjJw
 */
$channelId = 'UC1pfwysp1EC5P4qE-Eo97HA';
$arParams = [
		//the folder where cache files will be located
		'cache-path' => 'cache/',
		//number of seconds during which each API response will be stored in the cache file
		'cache-time' => 28800,
	];

//class loader
spl_autoload_register(function($class){
	if(is_file($path = '../classes/'.str_replace('\\','/',$class).'.php'))
		include($path);
});

$listId = filter_input(INPUT_GET,'list');
$videoId = filter_input(INPUT_GET,'video');

$channel = new \pgood\youtube\channel($channelId,$apiKey,$arParams);
$list = $listId ? new \pgood\youtube\playlist($listId,$apiKey,$arParams) : null;

//video detail page
if($videoId){
	$template = new \pgood\xml\template('xslt/video.xsl');
	$video = new \pgood\youtube\video($videoId,$apiKey,$arParams);
	$xml = new \pgood\xml\xml();
	$xml->de('video');
	$xml->de()->url = '?'.http_build_query(['list' => $list->id(),'video' => $video->id()]);
	$eList = $xml->de()->append('playlist');
	$eList->title = $list->title();
	$eList->url = '?'.http_build_query(['list' => $list->id()]);
	$eChannel = $xml->de()->append('channel');
	$eChannel->title = $channel->title();
	$eChannel->url = '.';
	$xml->de()->append('title')->text($video->title());
	$xml->de()->append('player')->text($video->embedHtml(800,450,'embed-responsive-item'));
	if($video->description())
		$xml->de()->append('desc')->text($video->description());
	if($video->image())
		$xml->de()->append('img')->src = $video->image('medium');
}
//playlist page
elseif($listId){
	if($arVideos = $list->videos()){
		$template = new \pgood\xml\template('xslt/playlist.xsl');
		$xml = new \pgood\xml\xml();
		$xml->de('playlist');
		$xml->de()->title = $list->title();
		$xml->de()->url = '?'.http_build_query(['list' => $list->id()]);
		$eChannel = $xml->de()->append('channel');
		$eChannel->title = $channel->title();
		$eChannel->url = '.';
		foreach($arVideos as $video){
			$eVideo = $xml->de()->append('video');
			$eVideo->id = $video->id();
			$eVideo->url = '?'.http_build_query(['list' => $list->id(),'video' => $video->id()]);
			$eVideo->append('title')->text($video->title());
			if($video->description())
				$eVideo->append('desc')->text($video->description());
			if($video->image())
				$eVideo->append('img')->src = $video->image('medium');
		}
	}
}
//channel page
else{
	if($arLists = $channel->playlists()){
		$template = new \pgood\xml\template('xslt/channel.xsl');
		$xml = new \pgood\xml\xml();
		$xml->de('channel');
		$xml->de()->title = $channel->title();
		$xml->de()->{'youtube-url'} = $channel->url();
		foreach($arLists as $list){
			$eList = $xml->de()->append('playlist');
			$eList->id = $list->id();
			$eList->url = '?'.http_build_query(['list' => $list->id()]);
			$eList->append('title')->text($list->title());
			if($list->description())
				$eList->append('desc')->text($list->description());
			if($list->image())
				$eList->append('img')->src = $list->image('medium');
		}
	}
}


?><!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
	<!-- Icons -->
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">

	<title><?=$channel->title();?> - Youtube Channel</title>

	<style>
.max-height-3{
   overflow: hidden;
   text-overflow: ellipsis;
   line-height: 21px;
   max-height: 63px;
}
.page-footer {
	padding: 2rem 0;
	background-color: #f9f9f9;
	border-top: .05rem solid #e5e5e5;
}
	</style>
</head>
<body>
	<main class="container mb-5"><?

		if($xml && $template)
			echo $template->transform($xml);	
	
	?></main>
	<footer class="page-footer">
		<div class="container">
			<span class="text-muted">developed by Pavel Khoroshkov, <a href="http://pgood.space"><i class="fas fa-link"></i> pgood.space</a>, <a href="https://github.com/pgooood" target="_blank"><i class="fab fa-github"></i> pgooood</a></span>
		</div>
	</footer>
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body>
</html>