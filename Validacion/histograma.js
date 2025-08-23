console.log("histograma.js cargado");

document.addEventListener("DOMContentLoaded", function () {
    const btnHistograma = document.getElementById("mostrarHistograma");
    const btnLineas = document.getElementById("mostrarGraficoLineas");

    // Histograma promedio por seccion (Barras)
    if (btnHistograma && datosGraficos.promediosPorSeccion) {
        btnHistograma.addEventListener("click", function () {
            const canvas = document.getElementById("graficoHistograma");
            canvas.style.display = "block";
            const ctx = canvas.getContext("2d");

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Sección 1', 'Sección 2', 'Sección 3', 'Sección 4'],
                    datasets: [{
                        label: 'Promedio de respuestas',
                        data: datosGraficos.promediosPorSeccion,
                        backgroundColor: ['#4e79a7', '#f28e2b', '#e15759', '#76b7b2'],
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        title: {
                            display: true,
                            text: 'Promedio de Respuestas por Sección'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 6
                        }
                    }
                }
            });
        });
    }

    // Histograma promedios por preguntas
    if (btnLineas && datosGraficos.promediosPorPregunta) {
        btnLineas.addEventListener("click", function () {
            const canvas = document.getElementById("graficoLineas");
            canvas.style.display = "block";
            const ctx = canvas.getContext("2d");

            const secciones = datosGraficos.promediosPorPregunta;
            const cantidadPreguntas = Math.max(...Object.values(secciones).map(arr => arr.length));
            const etiquetas = Array.from({ length: cantidadPreguntas }, (_, i) => `Pregunta ${i + 1}`);

            const colores = ['#4e79a7', '#f28e2b', '#e15759', '#76b7b2'];

            const datasets = Object.entries(secciones).map(([seccion, datos], idx) => ({
                label: seccion,
                data: datos,
                fill: false,
                borderColor: colores[idx % colores.length],
                tension: 0.2
            }));

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: etiquetas,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Promedios por Pregunta en Cada Sección'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 6
                        }
                    }
                }
            });
        });
    }
    const btnOcultar = document.getElementById("ocultarGraficas");

    if (btnOcultar) {
        btnOcultar.addEventListener("click", function () {
            const canvasHistograma = document.getElementById("graficoHistograma");
            const canvasLineas = document.getElementById("graficoLineas");

            canvasHistograma.style.display = "none";
            canvasLineas.style.display = "none";
        });
    }
});
