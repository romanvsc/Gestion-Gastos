<?php
/**
 * Página de inicio - Redirección automática
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Gastos - Bienvenida</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    </style>
</head>
<body class="bg-gray-50">
    
    <div class="gradient-bg min-h-screen flex items-center justify-center px-4">
        <div class="max-w-4xl w-full">
            
            <!-- Tarjeta principal -->
            <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                
                <!-- Header -->
                <div class="gradient-bg text-white text-center py-12 px-6">
                    <div class="inline-block bg-white bg-opacity-20 p-4 rounded-full mb-4">
                        <i class="fas fa-wallet text-5xl"></i>
                    </div>
                    <h1 class="text-4xl font-bold mb-3">Gestión de Gastos</h1>
                    <p class="text-lg opacity-90">Tu aplicación para control financiero personal</p>
                </div>
                
                <!-- Contenido -->
                <div class="p-8">
                    
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-3">¡Bienvenido!</h2>
                        <p class="text-gray-600">Elige una opción para comenzar</p>
                    </div>
                    
                    <!-- Opciones -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        
                        <!-- Opción 1: Ir a la aplicación -->
                        <a href="frontend/index.html" class="card-hover block bg-gradient-to-br from-indigo-50 to-purple-50 border-2 border-indigo-200 rounded-2xl p-6 text-center hover:border-indigo-400">
                            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Usar la Aplicación</h3>
                            <p class="text-gray-600 text-sm mb-4">Accede al dashboard y comienza a gestionar tus finanzas</p>
                            <span class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                Ir al Dashboard <i class="fas fa-arrow-right ml-2"></i>
                            </span>
                        </a>
                        
                        <!-- Opción 2: Test de instalación -->
                        <a href="backend/test.php" class="card-hover block bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-2xl p-6 text-center hover:border-green-400">
                            <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Verificar Instalación</h3>
                            <p class="text-gray-600 text-sm mb-4">Comprueba que todo esté configurado correctamente</p>
                            <span class="inline-block bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                Ejecutar Tests <i class="fas fa-check ml-2"></i>
                            </span>
                        </a>
                        
                    </div>
                    
                    <!-- Características -->
                    <div class="bg-gray-50 rounded-2xl p-6 mb-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-star text-yellow-500 mr-2"></i>
                            Características Principales
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-start space-x-3">
                                <i class="fas fa-calendar-alt text-indigo-600 mt-1"></i>
                                <div>
                                    <p class="font-medium text-gray-800">Calendario Mensual</p>
                                    <p class="text-sm text-gray-600">Visualiza tus gastos día por día</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i class="fas fa-chart-pie text-purple-600 mt-1"></i>
                                <div>
                                    <p class="font-medium text-gray-800">Análisis por Categoría</p>
                                    <p class="text-sm text-gray-600">Identifica en qué gastas más</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i class="fas fa-wallet text-green-600 mt-1"></i>
                                <div>
                                    <p class="font-medium text-gray-800">Control de Presupuesto</p>
                                    <p class="text-sm text-gray-600">Establece y monitorea tu presupuesto</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <i class="fas fa-mobile-alt text-blue-600 mt-1"></i>
                                <div>
                                    <p class="font-medium text-gray-800">Responsive Design</p>
                                    <p class="text-sm text-gray-600">Accede desde cualquier dispositivo</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Enlaces útiles -->
                    <div class="text-center">
                        <p class="text-gray-600 text-sm mb-3">¿Necesitas ayuda?</p>
                        <div class="flex justify-center space-x-4">
                            <a href="README.md" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                <i class="fas fa-book mr-1"></i>Documentación
                            </a>
                            <a href="INSTALACION.md" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                <i class="fas fa-download mr-1"></i>Guía de Instalación
                            </a>
                        </div>
                    </div>
                    
                </div>
                
                <!-- Footer -->
                <div class="bg-gray-50 text-center py-4 px-6 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        Desarrollado con <i class="fas fa-heart text-red-500"></i> para ayudarte a controlar tus finanzas
                    </p>
                </div>
                
            </div>
            
        </div>
    </div>
    
</body>
</html>
