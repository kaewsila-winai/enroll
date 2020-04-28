function initEnroll(text) {
  birthdayChanged('register_birthday', text);
  initProvince('register');
}

function initEnrollWrite() {
  $G('write_language').addEvent('change', function() {
    loader.location('index.php?module=enroll-write&language=' + this.value);
  });
}