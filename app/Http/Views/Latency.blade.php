<!--suppress JSUnresolvedReference -->
<div id="main1" style="width: 1024px; height: 512px;"></div>
<a id="download1" href="">Download AS PNG</a>
<div id="main2" style="width: 1024px; height: 512px;"></div>
<a id="download2" href="">Download AS PNG</a>
<script src="https://unpkg.com/echarts@5.5.0/dist/echarts.js"></script>
<script>
    const ldata = {
        data: {!! $data !!},
        time: {!! $time !!},
    }
</script>
<script>
    const charts1 = echarts.init(document.getElementById('main1'));
    const option1 = {
        xAxis: {
            name: 'Time',
            type: 'category',
            data: ldata.time,
        },
        yAxis: {
            name: 'Rate',
            type: 'value',
            max: 100,
            interval: 6.25,
        },
        grid: {
            top: '72px',
            left: '72px',
            right: '72px',
            bottom: '64px',
        },
        series: [{
            name: 'Rate',
            type: 'line',
            data: ldata.data,
            smooth: false,
            large: true,
        }],
    };
    charts1.setOption(option1);
    charts1.resize();
    window.onresize = function () {
        charts1.resize();
    };
    image = charts1.getDataURL();
    download = document.getElementById('download1');
    download.href = image;
    download.download = 'Latency.png';
</script>
<script>
    const charts2 = echarts.init(document.getElementById('main2'));
    const option2 = {
        xAxis: {
            name: 'Time',
            type: 'category',
            data: ldata.time,
        },
        yAxis: {
            name: 'Rate',
            type: 'value',
            max: 100,
            interval: 6.25,
        },
        grid: {
            top: '72px',
            left: '72px',
            right: '72px',
            bottom: '64px',
        },
        series: [{
            name: 'Rate',
            type: 'line',
            data: ldata.data,
            smooth: false,
            large: true,
        }],
        dataZoom: [{
            type: 'slider',
            startValue: 0,
            endValue: 100,
        }]
    };
    charts2.setOption(option2);
    charts2.resize();
    window.onresize = function () {
        charts2.resize();
    };
    image = charts2.getDataURL();
    download = document.getElementById('download2');
    download.href = image;
    download.download = 'Latency.png';
</script>
