var options = {
    chart: {
      type: 'line',
      height: '400px',
    },
    // colors: ['#f6c90f', '#313942', '#3b4851'],
    colors: ['#db7358', '#e7ded0', '#c26148'],
    series: [{
      name: search_insights_graph_label,
      data: search_insights_graph_data
    }],
    xaxis: {
      categories: search_insights_graph_days
    },
}
  
var chart = new ApexCharts(document.querySelector("#st_chart"), options);
  
chart.render();