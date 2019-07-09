var weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

// CLOCK

function timeDisplay() {
	var d = new Date();
	var s = d.getSeconds();
	if(s < 10) s = '0' + s;
	var m = d.getMinutes();
	if(m < 10) m = '0' + m;
	var h = d.getHours();
	document.getElementById('time').textContent = h + ":" + m + ":" + s;
}

// DATE

function dateDisplay() {
	var dObject = new Date();
	var dWeekday = weekdays[dObject.getDay()];
	var currentDate = dWeekday + ', ' + dObject.getDate() + '. ' + months[dObject.getMonth()] + ' ' + dObject.getFullYear();
	document.getElementById('date').textContent = currentDate;
}
dateDisplay();

// WEATHER

function updateWeather() {

	var weatherIcons = {
		'clear-day': '☀️',
		'clear-night': '🌃',
		'rain': '☔️',
		'snow': '❄️',
		'sleet': '🌧',
		'wind': '💨',
		'fog': '🌫',
		'cloudy': '☁️',
		'partly-cloudy-day': '🌤',
		'partly-cloudy-night': '🌃'
	};

	var request = new XMLHttpRequest();
	request.open('GET', URI + 'getWeather.php', true);

	request.onload = function() {
		if (request.status >= 200 && request.status < 400) {
			var data = JSON.parse(request.responseText);
			console.log(data);

			var cWeather = convertFtoC(data.currently.apparentTemperature) + '°C<br>' + weatherIcons[data.currently.icon];
			document.getElementById('currentWeather').innerHTML = cWeather;

			var hourlyWeather = '';
			for(var hour = 1; hour <= 24; hour++) {
				hourlyWeather += '<li><span>' + getWeatherTime(data.hourly.data[hour].time) + '</span><br>' + convertFtoC(data.hourly.data[hour].apparentTemperature) + '°C<br>' + weatherIcons[data.hourly.data[hour].icon] + '</li>';
			}
			document.getElementById('hourlyWeather').innerHTML = hourlyWeather;
			document.getElementById('hourlyWeather').scrollLeft = 0;

			var dailyWeather = '';
			for(var day = 1; day <= 3; day++) {
				dailyWeather += '<li><span class="lower-intensity">' + getWeatherDate(data.daily.data[day].time) + '</span><br>' + convertFtoC(data.daily.data[day].apparentTemperatureHigh) + '↑ ' + convertFtoC(data.daily.data[day].apparentTemperatureLow) + '↓ ' + weatherIcons[data.daily.data[day].icon] + '<br><span class="lower-intensity">' + getSunString(data.daily.data[day].sunriseTime, data.daily.data[day].sunsetTime) + '</span></li>';
			}
			document.getElementById('dailyWeather').innerHTML = dailyWeather;

			document.querySelector('#todaysSunEvents').textContent = getSunString(data.daily.data[0].sunriseTime, data.daily.data[0].sunsetTime);
		} else {
			showError('Weather API returned an error. Check API status.');
		}
	};
	request.onerror = function() { showError('Error connecting to Weather API server. Offline?'); };
	request.send();
}

function convertFtoC(temp) {
	return Math.round((temp - 32) * 5 / 9);
}

function getWeatherTime(timestamp) {
	var date = new Date(timestamp * 1000);
	var hours = date.getHours();
	var minutes = date.getMinutes();
	if(minutes < 10) minutes = '0' + minutes;
	return hours + ':' + minutes;
}

function getWeatherDate(timestamp) {
	var date = new Date(timestamp * 1000);
	return weekdays[date.getDay()].substring(0,2) + '., ' + date.getDate() + '. ' + months[date.getMonth()];
}

function getSunString(sunriseTime, sunsetTime) {
	var sunrise = new Date(sunriseTime * 1000);
	var sunriseClock = sunrise.getHours() + ':' + sunrise.getMinutes().toString().padStart(2, '0');
	var sunset = new Date(sunsetTime * 1000);
	var sunsetClock = sunset.getHours() + ':' + sunset.getMinutes().toString().padStart(2, '0');
	return sunriseClock + ' – ' + sunsetClock;
}

updateWeather();

// PREVENT DISPLAY SLEEP MODE

var noSleep = new NoSleep();
var wakeLockEnabled = false;

function noSleepActivate() {
	noSleep.enable(); // keep the screen on!
	wakeLockEnabled = true;
	sleepButton.style.backgroundColor = '#fff';
	console.log('screen on');
}

function noSleepDeactivate() {
	noSleep.disable(); // let the screen turn off.
	wakeLockEnabled = false;
	sleepButton.style.backgroundColor = '#000';
	console.log('screen off');
}

var sleepButton = document.querySelector('#toggleScreen');
sleepButton.addEventListener('click', function() {
	if (!wakeLockEnabled) {
		noSleepActivate();
	} else {
		noSleepDeactivate();
	}
	sleepButton.blur();
}, false);

// LOAD UPCOMING ITEMS

function loadUpcomingDays() {
	var request = new XMLHttpRequest();
	request.open('GET', URI + 'getUpcoming.php', true);

	request.onload = function() {
		if (request.status >= 200 && request.status < 400) {
			var data = request.responseText;

			// FILL UPCOMING LIST WITH FULL PLAN
			document.querySelector('#upcoming').innerHTML = data;

			// PUT TODAY’S STUFF ON THE LEFT
			document.querySelectorAll('#currentDay > .day-box').forEach(function(previousDayItem) {
				previousDayItem.remove();
			});
			var currentDayBox = document.querySelector('#upcoming .day-box:first-child');
			var newBox = currentDayBox.cloneNode(true);
			var currentDayContainer = document.querySelector('#currentDay');
			currentDayContainer.appendChild(newBox);
			currentDayBox.remove();
			document.querySelector('#currentDay .day-box .date').textContent = 'Heute';

		} else {
			showError('Upcoming items server could be reached but returned an error.');
		}
	};
	request.onerror = function() { showError('Error while loading upcoming items. Offline?'); };
	request.send();
}
loadUpcomingDays();

// TIMED ACTIONS

function updateThings() {
	var d = new Date();
	var H = d.getHours();
	var m = d.getMinutes();
	var s = d.getSeconds();

	// CLOCK: EVERY SECOND
	timeDisplay();

	// WEATHER: EVERY HOUR
	if(m == 0 && s == 0) {
		updateWeather();
	}

	// SCREEN: TURN ON AT 7, OFF AT 22
	if(H == 7 && m == 0 && s == 0) {
		noSleepActivate();
	}
	if(H == 22 && m == 0 && s == 0) {
		noSleepDeactivate();
	}

	// DAILY AGENDA AND NEW UPCOMING THINGS: EVERY 10 MINUTES
	if(m % 10 == 0 && s == 0) {
		loadUpcomingDays();
	}

	// RELOAD THE DATE: AT 0:00
	if(H == 0 && m == 0 && s == 0) {
		// DATE
		dateDisplay();
	}
}
setInterval(updateThings, 1000);

// HELPERS

// preselect "annually" as interval when birthday type is chosen
// also show time selects when datetime type is chosen
var typeSelect = document.querySelector('select#type');
if(typeSelect != null) {
	typeSelect.addEventListener('change', function() {
		if(this.value == 'birthday') document.querySelector('select#interval').value = 'annually';
		if(this.value == 'datetime') {
			document.querySelector('.add-screen .datetime-box').style.display = 'block';
		} else {
			document.querySelector('.add-screen .datetime-box').style.display = 'none';
		}
	});
}

// on date change search for events for that day and display them
document.querySelectorAll('form [name="day"], form [name="month"], form [name="year"]').forEach(function(el) {
	el.addEventListener('change', function(ev) {
		var date = getSelectedDate();
		var request = new XMLHttpRequest();
		request.open('GET', URI + 'getUpcoming.php?action=infoforday&day=' + date, true);
		request.onload = function() {
			if (request.status >= 200 && request.status < 400) {
				var data = JSON.parse(request.responseText);
				var infoBox = document.querySelector('#dateItemsInfoBox');
				if(data) {
					var infoString = data.length + ' item';
					if(data.length != 1) infoString += 's';
					infoString += ' on this date: ';
					data.forEach(function(item) {
						infoString = infoString + '<a class="info-box-item" href="' + URI + '?screen=add&id=' + item[0] + '">' + item[2];
						if(item[4] == 'birthday') infoString += ' 🎈';
						infoString += '</a>';
					});

					infoBox.innerHTML = infoString;
					infoBox.style.display = "block";
				} else {
					infoBox.style.display = "none";
				}
			} else {
				showError('Server could be reached but returned an error. Offline?');
			}
		};
		request.onerror = function() { showError('Error while trying to load items of date ' + date + '.'); };
		request.send();
	});
});

// get currently selected date
function getSelectedDate() {
	var year = document.querySelector('form [name="year"]').value;
	var month = document.querySelector('form [name="month"]').value;
	if(month < 10) month = '0' + month;
	var day = document.querySelector('form [name="day"]').value;
	if(day < 10) day = '0' + day;
	return year + '-' + month + '-' + day;
}

// show error message
function showError(messageText) {
	var date = new Date();
	var niceDate = date.getDate() + '. ' + months[date.getMonth()] + ' ' + date.getFullYear() + ' ' + date.getHours() + ':' + date.getMinutes().toString().padStart(2, '0') + ':' + date.getSeconds().toString().padStart(2, '0');
	document.querySelector('#messageTime').textContent = '[' + niceDate + ']';
	document.querySelector('#messageText').innerHTML = messageText;
	document.querySelector('#errorMessage').style.display = 'block';
}

// populate days select menu with weekday names
function populateDays(setCurrentDay = false) {
	var daysSelect = document.querySelector('form [name="day"]');
	var selectedDay = (daysSelect ? daysSelect.options[daysSelect.selectedIndex].value : '');

	if(daysSelect) {
		daysSelect.querySelectorAll('option').forEach(function(childEl) { childEl.remove(); });
		for(var i = 1; i <= 31; i++) {
			var day = (i < 10) ? '0' + i : i;
			var month = document.querySelector('form [name="month"]').value - 1;
			var year = document.querySelector('form [name="year"]').value;
			var dObject = new Date(year, month, day);
			var dWeekday = weekdays[dObject.getDay()].substr(0, 3) + '., ';

			var newDay = document.createElement('option');
			newDay.value = i;
			newDay.textContent = dWeekday + i + '.';
			var today = new Date();
			if(i == selectedDay) newDay.setAttribute('selected', 'selected');
			if(selectedDay != today.getDate() && i == today.getDate() && setCurrentDay) newDay.setAttribute('selected', 'selected');

			daysSelect.appendChild(newDay);
		}
	}
}
populateDays(true);

document.querySelectorAll('form [name="month"], form [name="year"]').forEach(function(dateSelect) {
	dateSelect.addEventListener('change', function(event) {
		populateDays();
	});
});