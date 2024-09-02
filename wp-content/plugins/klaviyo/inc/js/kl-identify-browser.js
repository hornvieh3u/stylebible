/**
 * Identify browser on site if they are logged in.
 *
 * Object possibly containing user/commenter email address:
 * @typedef {Object} klUser
 *   @property {string} currect_user_email - Email of logged in user
 *   @property {string} commenter_email - Email of logged in commenter
 *
 */


function klIdentifyBrowser(klUser) {
  var _learnq = window._learnq || [];
  if (klUser.current_user_email) {
    _learnq.push(["identify", {
      "$email": klUser.current_user_email
    }]);
  } else {
    // See if current user is a commenter
    if (klUser.commenter_email) {
      _learnq.push(["identify", {
        "$email": klUser.commenter_email
      }]);
    }
  }
}

window.addEventListener("load", function() {
    klIdentifyBrowser(klUser);
});