function initOrderChart(labels, data){
    const ctx = document.getElementById('orderChart').getContext('2d');
    new Chart(ctx,{
        type:'doughnut',
        data:{
            labels: labels,
            datasets:[{
                data: data,
                backgroundColor: ['#4f46e5','#6366f1','#10b981','#ef4444'],
                borderWidth:1
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            plugins:{
                legend:{
                    position:'bottom',
                    labels:{font:{size:12}}
                }
            }
        }
    });
}
