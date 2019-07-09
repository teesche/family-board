<?php

require_once 'inc/config.inc.php';
require_once 'inc/DB.class.php';
DB::init();
DB::getConnection();

$message = isset($_REQUEST['message']) ? $_REQUEST['message'] : '';

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'save') {
	$fields = ['name', 'type', 'interval', 'day', 'month', 'year'];
	foreach($fields as $field) if(!isset($_REQUEST[$field])) exit('Please fill in all required fields.');

	$date = sprintf('%04d', $_REQUEST['year']) . '-' . sprintf('%02d', $_REQUEST['month']) . '-' . sprintf('%02d', $_REQUEST['day']) . ' ' . sprintf('%02d', $_REQUEST['hours']) . ':' . sprintf('%02d', $_REQUEST['minutes']) . ':00';
	$values = [trim($_REQUEST['name']), trim($_REQUEST['infobox']), $_REQUEST['type'], $date, $_REQUEST['interval']];

	if(isset($_REQUEST['id'])) {
		$values[] = $_REQUEST['id'];
		DB::update('update familyplanner set name = ?, comments = ?, type = ?, duedate = ?, `interval` = ? where id = ? limit 1', $values);
	} else {
		DB::update('insert into familyplanner set entered = now(), name = ?, comments = ?, type = ?, duedate = ?, `interval` = ?', $values);
	}

	header("Location: " . URI . "?message=saved");
	exit;
}

if(isset($_REQUEST['delete']) && is_numeric($_REQUEST['delete'])) {
	DB::update('delete from familyplanner where id = ? limit 1', [$_REQUEST['delete']]);
	header("Location: " . URI . "?message=deleted");
	exit;
}

?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Family Plan</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="css/style.css">
    </head>
    <body>
		<div class="message error-message" id="errorMessage">
			<a href="<?= URI; ?>" class="cancel-add" id="closeBox">⨉</a>
			<p><span id="messageTime"></span> <span id="messageText"></span></p>
		</div>
		<?php if(isset($_REQUEST['screen']) && $_REQUEST['screen'] == 'add') : ?>

			<?php
				if(isset($_REQUEST['id']) && is_numeric($_REQUEST['id']))
					$entry = DB::fetchRow('select * from familyplanner where id = ?', [$_REQUEST['id']]);
			?>

			<div class="add-screen">
				<a href="<?= URI; ?>" class="cancel-add" id="cancelAdd">⨉</a>
				<h1><?php if(isset($entry['id'])) echo 'Edit item'; else echo 'Create new item'; ?></h1>
				<form action="<?= URI; ?>?action=save" method="post">
					<?php if(isset($entry['id'])) : ?>
						<input type="hidden" name="id" value="<?= $entry['id']; ?>">
					<?php endif; ?>

					<label for="name">Name</label>
					<input type="text" name="name" id="name" placeholder="i.e. Julie’s field trip" autofocus required <?php if(isset($entry['name'])) echo 'value="' . $entry['name'] . '"'; ?>>

					<label for="type">Type</label>
					<select name="type" id="type" required>
						<option value="normal" <?php if(isset($entry['type']) && $entry['type'] == 'normal') echo 'selected="selected"'; ?>>Standard</option>
						<option value="datetime" <?php if(isset($entry['type']) && $entry['type'] == 'datetime') echo 'selected="selected"'; ?>>With time</option>
						<option value="birthday" <?php if(isset($entry['type']) && $entry['type'] == 'birthday') echo 'selected="selected"'; ?>>Birthday</option>
					</select>

					<label for="interval">Interval</label>
					<select name="interval" id="interval" required>
						<option value="once" <?php if(isset($entry['interval']) && $entry['interval'] == 'once') echo 'selected="selected"'; ?>>Once</option>
						<option value="daily" <?php if(isset($entry['interval']) && $entry['interval'] == 'daily') echo 'selected="selected"'; ?>>Daily</option>
						<option value="weekly" <?php if(isset($entry['interval']) && $entry['interval'] == 'weekly') echo 'selected="selected"'; ?>>Weekly</option>
						<option value="monthly" <?php if(isset($entry['interval']) && $entry['interval'] == 'monthly') echo 'selected="selected"'; ?>>Monthly</option>
						<option value="quarterly" <?php if(isset($entry['interval']) && $entry['interval'] == 'quarterly') echo 'selected="selected"'; ?>>Quarterly</option>
						<option value="annually" <?php if(isset($entry['interval']) && $entry['interval'] == 'annually') echo 'selected="selected"'; ?>>Annually</option>
					</select>

					<label for="day">Date</label>
					<select name="day" class="date" required>
						<?php for($i = 1; $i <= 31; $i++) { ?>
							<option value="<?= $i ?>" <?php if(isset($entry['duedate']) && $i == intval(substr($entry['duedate'], 8, 2))) echo 'selected="selected"'; elseif($i == date('d')) echo 'selected="selected"'; ?>><?= $i ?></option>
						<?php } ?>
					</select>
					<select name="month" class="date" required>
						<?php for($i = 1; $i <= 12; $i++) { ?>
							<option value="<?= $i ?>" <?php if(isset($entry['duedate']) && $i == intval(substr($entry['duedate'], 5, 2))) echo 'selected="selected"'; elseif($i == date('m')) echo 'selected="selected"'; ?>><?= $months[$i -1] ?></option>
						<?php } ?>
					</select>
					<select name="year" class="date" required>
						<option value="0000"></option>
						<?php for($i = 1900; $i <= date('Y') + 10; $i++) { ?>
							<option value="<?= $i ?>" <?php if(isset($entry['duedate']) && $i == intval(substr($entry['duedate'], 0, 4))) echo 'selected="selected"'; elseif($i == date('Y') && !isset($entry['duedate'])) echo 'selected="selected"'; ?>><?= $i ?></option>
						<?php } ?>
						<option value="0000"></option>
					</select>
					<p class="date-items-info-box" id="dateItemsInfoBox"></p>

					<div class="datetime-box" <?php if(isset($entry['type']) && $entry['type'] == 'datetime') : ?>style="display: block;"<?php endif; ?>>
						<label for="hours">Time</label>
						<select name="hours" class="add-time">
							<?php for($hour = 0; $hour <= 23; $hour++) : ?>
								<option value="<?= $hour; ?>" <?php if(isset($entry['duedate']) && $hour == intval(substr($entry['duedate'], 11, 2))) echo 'selected="selected"'; ?>><?= $hour ?></option>
							<?php endfor; ?>
						</select>
						<select name="minutes" class="add-time">
							<?php for($minute = 0; $minute <= 55; $minute += 5) : ?>
								<option value="<?= $minute ?>" <?php if(isset($entry['duedate']) && $minute == intval(substr($entry['duedate'], 14, 2))) echo 'selected="selected"'; ?>><?= $minute ?></option>
							<?php endfor; ?>
						</select>
					</div>

					<label for="infobox">Comment</label>
					<input type="text" name="infobox" id="infobox" placeholder="i.e. Don’t forget backpack!" <?php if(isset($entry['comments'])) echo 'value="' . $entry['comments'] . '"'; ?>>
					<input type="submit" value="Save">
					<?php if(isset($entry['id'])) : ?>
						<a class="delete-item" href="<?= URI; ?>?delete=<?= $entry['id']; ?>" onclick="return confirm('Really delete?')">Delete</a>
					<?php endif; ?>
				</form>
			</div>
		<?php endif; ?>
		<input type="button" id="toggleScreen" class="toggle-screen black" value="💡">
		<a href="<?= URI; ?>?screen=add" id="toggleAdd" class="toggle-add">🆕</a>
		<div class="half c1">
			<!-- Info regarding today will be put in here by JS -->
			<div class="datetime">
				<p id="time" class="clock"><?php echo date('H:i:s'); ?></p>
				<p id="date" class="date"></p>
				<p id="todaysSunEvents" class="todays-sun-events lower-intensity"></p>
				<p class="current-weather" id="currentWeather"></p>
				<ul class="upcoming-weather" id="hourlyWeather"></ul>
				<ul class="daily-weather" id="dailyWeather"></ul>
			</div>
			<div class="current-day" id="currentDay"></div>
			<?php if($message == 'saved') : ?>
				<p class="message">
					<a href="<?= URI; ?>" class="cancel-add" id="cancelAdd">⨉</a>
					Saved. <a href="<?= URI; ?>?screen=add">Save new item?</a>
				</p>
			<?php elseif($message == 'deleted') : ?>
				<p class="message">
					<a href="<?= URI; ?>" class="cancel-add" id="cancelAdd">⨉</a>
					Item deleted.
				</p>
			<?php endif; ?>

		</div>
		<div class="half c2" id="upcoming">
			<!-- Upcoming items will be put in here by JS -->
		</div>
		<script src="js/nosleep.js"></script>
		<script>
			var URI = <?= URI; ?>;
		</script>
		<script src="js/main.js"></script>
	</body>
</html>