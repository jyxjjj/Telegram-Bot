<div id="main" style="width: {{ $count * 8 }}px; height: 512px;"></div>
<a id="download" href="">Download AS PNG</a>
<script src="https://unpkg.com/echarts@5.5.0/dist/echarts.js"></script>
<script>
    const charts = echarts.init(document.getElementById('main'));
    const option = {
        xAxis: {
            name: 'Time',
            type: 'category',
            data: {!! $time !!},
        },
        yAxis: {
            name: 'Rate',
            type: 'value',
            max: 100,
            interval: 6.25,
        },
        grid: {
            top: '64px',
            left: '64px',
            right: '64px',
            bottom: '32px',
        },
        series: [{
            name: 'Rate',
            type: 'line',
            data: {!! $data !!},
            smooth: true,
            large: true,
        }],
    };
    charts.setOption(option);
    charts.resize();
    window.onresize = function () {
        charts.resize();
    };
    image = charts.getDataURL();
    download = document.getElementById('download');
    download.href = image;
    download.download = 'Latency.png';
</script>
