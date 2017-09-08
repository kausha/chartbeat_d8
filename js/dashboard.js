/**
 * @file
 * Attaches behaviors for the chartbeat module.
 */

(function ($) {
  if(drupalSettings.chartbeat.drupal_chartbeat) {
    var api_key = drupalSettings.chartbeat.drupal_chartbeat.api_key,
        host = drupalSettings.chartbeat.drupal_chartbeat.base_url,
        config = {
          'api': 'http://api.chartbeat.com/live/quickstats/v3/?apikey=' + api_key +'&host=' + host,
          'element': 'chartbeat-widget-sitetotal',
        };
      new SiteTotal(config);
    }

    
    var oldonload = $(window).load;
    $(window).load = ((typeof $(window).load) != 'function') ?
       loadChartbeat : function() { oldonload(); loadChartbeat();};

    function loadChartbeat() {
      window._sf_endpt = (new Date()).getTime();
   	  $('<script>')
   	  .attr('language', 'javascript')
      .attr('src',
         (("https:" == document.location.protocol) ? "https://s3.amazonaws.com/" : "http://") +
         "static.chartbeat.com/js/chartbeat.js")
      .appendTo('body');
    }

})(jQuery, drupalSettings);