﻿
			<li>
				<h2><?php echo $language_pack['top10players']; ?></h2>
				<ul>
<?php foreach ($template_properties['top10players'] as $rank => $top10player): ?>
					<li>
						<div style="float: left; text-align: left; width: 23px;"><?php echo $rank; ?>.</div><span style="position:relative;min-width:150px;max-width:200px;overflow:hidden;white-space:nowrap;"><?php echo $top10player['flag']; ?><a href="player.php?steamid=<?php echo htmlentities($top10player['steamid']); ?>"><?php echo $top10player['name']; ?></a></span>
					</li>
<?php endforeach; ?>
			</li>
