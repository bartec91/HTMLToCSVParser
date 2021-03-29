<?php

include 'inc/simple_html_dom.php';

function arrayToCSV($cfilename)
{
    $url = 'http://estoremedia.space/DataIT/';
    $nextUrl = $url;
    $htmlPage = file_get_html($nextUrl);

    $temp = $htmlPage->find('ul.pagination li a.next');
    $tempArray = array();
    foreach($temp as $k => $v) {
        $tempArray[$k] = $v->{'data-page'};
    }
    $maxAttrVal = max($tempArray);

    $currentAttrVal = 1;
    while($currentAttrVal <= $maxAttrVal) {
        $nextUrl = $url . "index.php?page=".$currentAttrVal;
        $htmlPage = file_get_html($nextUrl);
        foreach($htmlPage->find("div.col-lg-9 div.col-lg-4.col-md-6 div.card") as $p) {
            $product = array();
            $product['name'] = $p->find('div.card-body h4.card-title a')[0]->{'data-name'};
            $product['link'] = $url . $p->find('div.card-body h4.card-title a')[0]->href;
            $product['img'] = $p->find('a img.card-img-top')[0]->src;
            $product['price'] = $p->find('div.card-body h5')[0]->innertext;
            $count_rates = preg_match('#\((.*?)\)#', $p->find('div.card-footer small')[0]->innertext, $match);
            $product['count_rates'] = $match[1];
            $data[] = $product;
        }

        $currentAttrVal++;
    }

    if(is_array($data)) {
        $fp = fopen($cfilename, 'w');
        $header = false;
        foreach ($data as $row)
        {
            if (empty($header))
            {
                $header = array_keys($row);
                fputcsv($fp, $header);
                $header = array_flip($header);
            }
            fputcsv($fp, array_merge($header, $row));
        }
    }

    fclose($fp);
    return;
}

$csvFilename = '../products.csv';
if(file_exists($csvFilename)) {
    unlink($csvFilename);
}
arrayToCSV($csvFilename);
echo 'Successfully converted HTML to csv file. <a href="' . $csvFilename . '" target="_blank">Click here to open it.</a>';