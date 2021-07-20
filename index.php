<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>PRH-Scraper</title>
</head>
<body>
    <div class="container">
        <form method="POST">
            <label for="toimiala">Toimiala: </label>
            <input type="text" id="toimiala" name="b_line">
            <label for="kaupunki">Kaupunki: </label>
            <input type="text" name="city" id="kaupunki">
            <input type="submit" name="search" value="Hae">
        </form>
    </div>
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
    $b_line  = $_POST["b_line"];
    $city  = $_POST["city"];
    $url = "https://avoindata.prh.fi/bis/v1?totalResults=true&maxResults=3&resultsFrom=0&registeredOffice=".$city."&businessLine=".$b_line."&companyRegistrationFrom=2014-02-28";

    //$result_array = json_decode(file_get_contents($url));
    $result_array = json_decode(curl_get_contents($url));

    $results = $result_array->results;
    if (count($results) > 0)
    {
        print count($results)."<br>";
        echo "<table style='width:95%;'><tr><th>Company</th><th>Business ID</th><th>Phone</th><th>Street Address</th><th>Post Code</th><th>Website</th></tr>";
        foreach ($results as $key => $value) {
            echo "<tr>";
            $data = file_get_contents("https://avoindata.prh.fi/tr/v1/".$value->businessId);
            $data = json_decode($data);
            $data = $data->results;
            foreach ($data as $key => $value) {
                echo "<td>".$value->name."</td>";
                echo "<td>".$value->businessId."</td>";
                $phone = $value->addresses[0]->phone;
                echo "<td>".$phone."</td>";
                echo "<td>".$value->addresses[0]->street."</td>";
                echo "<td>".$value->addresses[0]->postCode."</td>";
                $website = $value->addresses[0]->website;
                if ($website && !strstr($website, "www."))
                    $website = "www.".$website;
                echo "<td>".$website."</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
}

?>