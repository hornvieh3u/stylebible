/**
 * WCK Viewed Product
 *
 * Incoming product object
 * @typedef {Object} item
 *   @property {string} title - Product name
 *   @property {int} product_id - Parent product ID
 *   @property {int} variant_id - Product ID
 *   @property {string} url - Product permalink
 *   @property {string} image_url - Product image url
 *   @property {float} price - Product price
 *   @property {array} categories - Product categories (array of strings)
 *
 * Unfortunately wp_localize_script converts all variables to strings :( so we
 * will have to re-parse ints and floats.
 * See note in - https://codex.wordpress.org/Function_Reference/wp_localize_script
 *
 */

var _learnq = _learnq || [];

var viewed_item = {
    'Title': item.title,
    'ItemId': parseInt(item.product_id),
    'ProductID': parseInt(item.product_id),
    'variantId': parseInt(item.variant_id),
    'Categories': item.categories,
    'ImageUrl': item.image_url,
    'Url': item.url,
    'Metadata': {
        'Price': parseFloat(item.price),
    }
};

var track_viewed_item = {
    'Title': item.title,
    'ItemId': parseInt(item.product_id),
    'variantId': parseInt(item.variant_id),
    'Categories': item.categories,
    'ImageUrl': item.image_url,
    'Url': item.url,
    'Metadata': {
        'Price': parseFloat(item.price),
    }
};

_learnq.push(['track', 'Viewed Product', viewed_item]);
_learnq.push(['trackViewedItem', track_viewed_item]);
