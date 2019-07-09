<?php

require_once '../inc/config.inc.php';
require_once '../inc/DB.class.php';
DB::init();
DB::getConnection();

$planItems = DB::fetchAll('select *, date_format(duedate, "%Y") as y, date_format(duedate, "%m") as m, date_format(duedate, "%d") as d, date_format(duedate, "%Y-%m-%d") as ymd, date_format(duedate, "%m-%d") as md from familyplanner order by field(type, "birthday", "normal", "datetime"), field(`interval`, "once", "daily", "weekly", "monthly", "quarterly", "annually"), duedate asc');

$daysToShow = 366;
for($day = 0; $day < $daysToShow; $day++) {
    $days[date('Y-m-d', strtotime("+" . $day . ' day', time()))] = [];
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'infoforday') {
    if(isset($_REQUEST['day'])) {
        // find out what happens on that day
        $daysItems = getItemsForDate($planItems, $_REQUEST['day']);
        echo json_encode($daysItems, true);
        exit;
    }
}

foreach($days as $dayDate => $dayItems) {
    $days[$dayDate] = getItemsForDate($planItems, $dayDate);
}

function getItemsForDate($planItems, $date) {

    foreach($planItems as $planItem) {

        if($planItem['interval'] == 'once' && $planItem['ymd'] == $date)
            $returnItems[] = $planItem;
        if($planItem['interval'] == 'annually' && $planItem['md'] == substr($date, 5, 5))
            $returnItems[] = $planItem;
        if($planItem['interval'] == 'quarterly' && $planItem['md'] == substr($date, 5, 5)
            || ($planItem['interval'] == 'quarterly' && ($planItem['md'] == sprintf('%02d', substr($date, 5, 2) - 3) . '-' . substr($date, 8, 2)))
            || ($planItem['interval'] == 'quarterly' && ($planItem['md'] == sprintf('%02d', substr($date, 5, 2) - 6) . '-' . substr($date, 8, 2)))
            || ($planItem['interval'] == 'quarterly' && ($planItem['md'] == sprintf('%02d', substr($date, 5, 2) - 3) . '-' . substr($date, 8, 2))))
            $returnItems = $planItem;
        if($planItem['interval'] == 'monthly' && $planItem['d'] == substr($date, 8, 2))
            $returnItems[] = $planItem;
        if($planItem['interval'] == 'weekly' && (date('N', strtotime($planItem['ymd'])) == date('N', strtotime(substr($date, 0, 10)))))
            $returnItems[] = $planItem;
        if($planItem['interval'] == 'daily')
            $returnItems[] = $planItem;
    }
    return $returnItems;
}

$html = '';
foreach($days as $dayDate => $dayItems) {
    $html .= '<div class="day-box">';
    $html .= '<p class="date">' . $weekdays[date('N', strtotime($dayDate))] . ', ' . date('j', strtotime($dayDate)) . '. ' . $months[date('m', strtotime($dayDate)) - 1] . (date('Y') != substr($dayDate, 0, 4) ? ' ' . substr($dayDate, 0, 4) : '') . '</p>';

    foreach($dayItems as $dayItem) {

        $html .= '<h3><a href="' . URI . '?screen=add&id=';
        $html .= $dayItem['id'];
        $html .= '">✏️</a> ';
        $html .= $dayItem['name'];
        if($dayItem['type'] == 'birthday' && $dayItem['y'] != '0000') {
            $age = substr($dayDate, 0, 4) - substr($dayItem['duedate'], 0, 4);
            $html .= '<span class="birthday-age">' . $age . '</span>';
        }
        if($dayItem['type'] == 'birthday') $html .= ' 🎈';

        if($dayItem['type'] == 'datetime') {
            $html .= ' <small class="lower-intensity">' . substr($dayItem['duedate'], 11, 5) . '</small>';
        }

        $html .= '</h3>';
        if(isset($dayItem['comments'])) $html .= '<p class="today-info-box">' . $dayItem['comments'] . '</p>';
    }
    $html .= '</div>';
}
echo $html;
exit;