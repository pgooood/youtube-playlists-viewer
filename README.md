# Youtube playlists viewer

Youtube playlists viewer might be useful if you want to show playlists and videos from any Youtube channel on your website

[Demo](http://pgood.space/userfiles/file/youtube-playlists-viewer/)

Examples
-------
```php
/* 
 * First you have to get your API key at https://developers.google.com/youtube/v3/getting-started
 */
$apiKey = '************';
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

$channel = new \pgood\youtube\channel($channelId,$apiKey,$arParams);

/* 
 * Channel playlists
 */
if($arLists = $channel->playlists()){
	?><h2>Playlists</h2><?
	foreach($arLists as $list){
		?><figure data-id="<?=$list->id();?>"><?
		if($list->image()){
			?><img src="<?=$list->image('standard');?>" alt=""><?
		}
		?><figcaption>
			<h3><?=$list->title();?></h3>
			<p><?=nl2br($list->description());?></p>
		</figcaption><?
		?></figure><?
	}
}

/* 
 * Playlist videos
 */
$listId = 'PLGfjhrxr3EEz3EeRucI_uV2U_YP08OfyR';
$list =  new \pgood\youtube\playlist($listId,$apiKey,$arParams);
if($arVideos = $list->videos()){
	?><h2>Videos</h2><?
	foreach($arVideos as $video){
		?><figure data-id="<?=$video->id();?>"><?
		if($video->image()){
			?><img src="<?=$video->image('standard');?>" alt=""><?
		}
		?><figcaption>
			<h3><?=$video->title();?></h3>
			<p><?=nl2br($video->description());?></p>
		</figcaption><?
		?></figure><?
	}
}

/* 
 * Video
 */
$videoId = '9rJoB7y6Ncs';
$video = new \pgood\youtube\video($videoId,$apiKey,$arParams);
?><h2><?=$video->title();?></h2><?
?><p><?=$video->embedHtml(800,450,'embed-responsive-item');?></p><?
?><p><?=nl2br($video->description());?></p><?
```
