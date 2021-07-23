<?php

if (isset($_POST["search"]))
{

	$fh = fopen("toimiala_1_20080101.csv", "r");
	$i = 0;
	while (($row = fgetcsv($fh, 0, ";")) !== false)
	{
		if (isset($row[2]) && !empty($row[2]) && strchr(strtolower($row[2]), strtolower($_POST["search"])))
		{
			?>
			<li class='list-item'><?=$row[2]?></li>
			<?php
		}
	}
}

?>
