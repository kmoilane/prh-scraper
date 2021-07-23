<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="styles.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<title>PRH-Scraper</title>
</head>
<body>
	<h1>Scrape Company Information From PRH Open Data</h1>
	<div class="container">
		<form method="POST">
			<label for="toimiala">Toimiala: </label>
			<input type="text" autocomplete="off" id="toimiala" name="b_line">
			<label for="kaupunki">Kaupunki: </label>
			<input type="text" name="city" id="kaupunki">
			<label for="maxResult">Max Results</label>
			<input type="number" name="max_results" id="maxResult">
			<input type="submit" name="search" value="Hae">
		</form>
	</div>
	<div>
		<ul id="output">

		</ul>
	</div>
<script>
	$(document).ready(function() {
		$('#toimiala').keyup(function () {
			var search = $(this).val();
			if ($(this).val().length > 4)
				{
					$.post("toimialat.php",
					{
					search:search
					},
					function(data, status){
						if (data !== "none") {
							$("#output").css("display", "block");
							$("#output").html(data);
						}
					});
				}
			else
				$("#output").css("display", "none");
		});

		$(".list-item").each(function(index) {
			$(this).on("click", function() {
				$("#toimiala").val() = $(this).val();
				$("#output").css("display", "none");
			});
		});
	});
</script>

</body>
</html>

<?php

function curl_get_contents($url)
{
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}

if (isset($_POST["search"]))
{
    $b_line  = htmlentities($_POST["b_line"]);
    $city  = htmlentities($_POST["city"]);
	$max_results = htmlentities($_POST["max_results"]);
    $url = "https://avoindata.prh.fi/bis/v1?totalResults=true&maxResults=".$max_results."&resultsFrom=0&registeredOffice=".$city."&businessLine=".$b_line."&companyRegistrationFrom=2014-02-28";
	$url = str_replace(" ", "%20", $url);

    //$result_array = json_decode(file_get_contents($url));
    $result_array = json_decode(curl_get_contents($url));

    $results = $result_array->results;
    if (count($results) > 0)
    {
        print "Total results with given parameters: ".$result_array->totalResults."<br>";
        echo "<table style='width:95%;'><tr><th>Company</th><th>Business ID</th><th>Registered</th><th>Phone</th><th>Street Address</th><th>Post Code</th><th>Website</th><th>Business Line</th></tr>";
        foreach ($results as $key => $value) {
            echo "<tr>";
            $data = file_get_contents($value->detailsUri);
            $data = json_decode($data);
            $newData = $data->results;
			if (empty($newData[0]->contactDetails))
				continue;
			$phone = "";
			$website = "";
            foreach ($newData as $key => $value) {
                echo "<td>".$value->name."</td>";
                echo "<td>".$value->businessId."</td>";
				echo "<td>".$value->registrationDate."</td>";
				for ($i=0; $i < count($value->contactDetails); $i++) {
					if ($value->contactDetails[$i]->type == "Puhelin" || $value->contactDetails[$i]->type == "Matkapuhelin")
                		$phone = $value->contactDetails[$i]->value;
					else if ($value->contactDetails[$i]->type == "Kotisivun www-osoite")
						$website = $value->contactDetails[$i]->value;
				}
                echo "<td>".$phone."</td>";
                echo "<td>".$value->addresses[0]->street."</td>";
                echo "<td>".$value->addresses[0]->postCode."</td>";
                if ($website != "" && !strstr($website, "www."))
                    $website = "www.".$website;
                echo "<td>".$website."</td>";
				if ($value->businessLines[0]->language == "FI")
					$businessLine = $value->businessLines[0]->name;
				else if ($value->businessLines[1]->language == "FI")
					$businessLine = $value->businessLines[1]->name;
				else if ($value->businessLines[1]->language == "FI")
					$businessLine = $value->businessLines[1]->name;
				echo "<td>".$businessLine."</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
}

?>
