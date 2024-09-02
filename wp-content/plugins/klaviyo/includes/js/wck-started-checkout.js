/**
 * WCK Started Checkout
 *
 * Incoming event object
 * @typedef {object} kl_checkout
 *   @property {string} email - Email of current logged in user
 *
 *   @property {object} event_data - Data for started checkout event
 *     @property {object} $extra - Event data
 *     @property {string} $service - Value will always be "woocommerce"
 *     @property {int} $value - Total value of checkout event
 *     @property {array} Categories - Product categories (array of strings)
 *     @property {string} Currency - Currency type
 *     @property {string} CurrencySymbol - Currency type symbol
 *     @property {array} ItemNames - List of items in the cart
 *
 */


/**
 * Attach event listeners to save billing fields.
 */

var identify_object = {
  'token': public_key.token,
  'properties': {}
};

var klaviyo_cookie_id = '__kla_id';

function makePublicAPIcall(endpoint, event_data) {
  data_param = btoa(unescape(encodeURIComponent(JSON.stringify(event_data))));
  jQuery.get('https://a.klaviyo.com/api/' + endpoint + '?data=' + data_param);
}

function getKlaviyoCookie() {
  var name = klaviyo_cookie_id + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var ca = decodedCookie.split(';');
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return atob(c.substring(name.length, c.length));
    }
  }
  return "";
}

function setKlaviyoCookie(cookie_data) {
  cvalue = btoa(JSON.stringify(cookie_data));
  var date = new Date();
  date.setTime(date.getTime() + (63072e6)); // adding 2 years in milliseconds to current time
  var expires = "expires=" + date.toUTCString();
  document.cookie = klaviyo_cookie_id + "=" + cvalue + ";" + expires + "; path=/";
}

function klIdentifyBillingField() {
  var billingFields = ["first_name", "last_name"];
  for (var i = 0; i < billingFields.length; i++) {
    (function () {
      var nameType = billingFields[i];
      jQuery('input[name="billing_' + nameType + '"]').change(function () {
        var email = jQuery('input[name="billing_email"]').val();
        if (email) {
          identify_properties = {
            '$email': email,
            [nameType]: jQuery.trim(jQuery(this).val())
          };
          setKlaviyoCookie(identify_properties);
          identify_object.properties = identify_properties;

          makePublicAPIcall('identify', identify_object);
        }
      })
    })();
  }
}

window.addEventListener("load", function () {
  // Custom checkouts/payment platforms may still load this file but won't
  // fire woocommerce_after_checkout_form hook to load checkout data.
  if (typeof kl_checkout === 'undefined') {
    return;
  }

  var WCK = WCK || {};
  WCK.trackStartedCheckout = function () {
    var event_object = {
      'token': public_key.token,
      'event': '$started_checkout',
      'customer_properties': {},
      'properties': kl_checkout.event_data
    };

    if (kl_checkout.email) {
      event_object.customer_properties['$email'] = kl_checkout.email;
    } else if (kl_checkout.exchange_id) {
      event_object.customer_properties['$exchange_id'] = kl_checkout.exchange_id;
    } else {
      return;
    }

    makePublicAPIcall('track', event_object);
  };

  var klCookie = getKlaviyoCookie();

  // Priority of emails for syncing Started Checkout event: Logged-in user,
  // cookied exchange ID, cookied email, billing email address
  if (kl_checkout.email !== "") {
    identify_object.properties = {
      '$email': kl_checkout.email
    };
    makePublicAPIcall('identify', identify_object);
    setKlaviyoCookie(identify_object.properties);
    WCK.trackStartedCheckout();
  } else if (klCookie && JSON.parse(klCookie).$exchange_id !== undefined) {
    kl_checkout.exchange_id = JSON.parse(klCookie).$exchange_id;
    WCK.trackStartedCheckout();
  } else if (klCookie && JSON.parse(klCookie).$email !== undefined) {
    kl_checkout.email = JSON.parse(klCookie).$email;
    WCK.trackStartedCheckout();
  } else {
    if (jQuery) {
      jQuery('input[name="billing_email"]').change(function () {
        var elem = jQuery(this),
          email = jQuery.trim(elem.val());

        if (email && /@/.test(email)) {
          var params = {
            "$email": email
          };
          var first_name = jQuery('input[name="billing_first_name"]').val();
          var last_name = jQuery('input[name="billing_last_name"]').val();
          if (first_name) {
            params["$first_name"] = first_name;
          }
          if (last_name) {
            params["$last_name"] = last_name;
          }

          setKlaviyoCookie(params);
          kl_checkout.email = params.$email;
          identify_object.properties = params;
          makePublicAPIcall('identify', identify_object);
          WCK.trackStartedCheckout();
        }
      });

      // Save billing fields
      klIdentifyBillingField();
    }
  }
});
