var signupButton = document.getElementById("signup-action-main");
var loginButton = document.getElementById("login-action-main");

function convertFormToJSON(formID) {
  var array = $(formID).serializeArray();
  var json = {};

  jQuery.each(array, function() {
    json[this.name] = this.value || '';
  });

  return json;
}
/*
  TODO:
  AJAX reveals information in console logs if "Log XMLHttpRequests" enabled,
  which means this will be revealed elsewhere.
  Obfuscate?
*/
/*
  This sends user information as plain text to the server, currently unencrypted
  When running on a remote server, USE TLS TO SEND ALL ENCRYPTED.
*/
function sendUserInfo(info) {
  $.ajax({
    url: "./php/password_management.php",
    type: "POST",
    dataType: "JSON",
    data: { name_first: info.name_first,
            name_last: info.name_last,
            email: info.email,
            password: info.password,
            usertype: info.usertype },
    success: function (data) {
      var status = data.status;

      if (status === "fail") {
        console.log(`Error: ${data.resp}`);
      }
      else if (status === "pass") {
        console.log(`Pass: ${data.verified}`);
      }
      else {
        console.log("No Response Received.");
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR.statusText, textStatus, errorThrown);
    }
  });
}

function doesUserExist(data) {
  $.ajax({
    url: "./php/password_management.php",
    type: "GET",
    dataType: "JSON",
    data: { email: data.email,
           password: data.password },
    success: function (data) {
      var status = data.status;

      if (status === "fail") {
        console.log(`Error: ${data.resp}`);
      }
      else if (status === "pass") {
        console.log(`Pass: ${data.sid} and Verified=${data.verified}`);
        if (data.verified === 1) {
          setCookie("EggsSession", data.sid);
        }
      }
      else {
        console.log("No Response Received.");
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      console.log(jqXHR.statusText, textStatus, errorThrown);
    }
  });
}

/*
  Cookie will expire when user closes browser if no duration set.
  https://stackoverflow.com/questions/14573223/set-cookie-and-get-cookie-with-javascript.
*/
function setCookie(name, value, days) {
  var expires = "";
  if (days) {
    var date = new Date();
    date.setTime(date.getTime() + (days*24*60*60*1000));
    expires = `; expires=${date.toUTCString()}`;
  }
  document.cookie = `${name}=${value}${expires}; path=/`;
}

signupButton.addEventListener("click", ()=>{
  sendUserInfo(convertFormToJSON("#signup-form"));
});

loginButton.addEventListener("click", ()=>{
  doesUserExist(convertFormToJSON("#login-form"));
});
