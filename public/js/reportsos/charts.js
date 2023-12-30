function sparklinePie(elem, values, colors, tags, width=200, height=200) {
   elem.sparkline(values, {
        type: 'pie',
        width: width+'px',
        height: height+'px',
        sliceColors: colors,
        borderWidth: 0,
        tooltipFormat: '<span style="color: {{color}}">&#9679;</span> {{offset:values}} {{offset:names}} ({{percent.1}}%)',
        tooltipValueLookups: {
            names: tags,
            values: values
        },
        offset: -90
    }); 
}

function sparklineDash(elem, color, height=30) {
   elem.sparkline([ 1, 5, 6, 10, 9, 12, 4, 9], {
        type: 'bar',
        barWidth: '4',
        barSpacing: '5',
        height: height+'px',        
        borderWidth: 0,
        barColor: color,
        tooltipFormat: ''       
    }); 
}