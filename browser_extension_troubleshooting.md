
Gracias por el detalle del error.

Este error: `Uncaught (in promise) Error: A listener indicated an asynchronous response by returning true, but the message channel closed before a response was received`

Generalmente **no está relacionado con el código de tu aplicación**, sino que suele ser causado por **extensiones de navegador**, especialmente en Google Chrome. Significa que alguna extensión está intentando responder a un mensaje de forma asíncrona, pero el canal de comunicación se cierra antes de que la respuesta pueda ser enviada.

Esto es importante porque:
*   Es muy probable que este error **no sea la causa directa** de que el modo oscuro no funcione en tu aplicación.
*   Sin embargo, una extensión problemática podría, en algunos casos, interferir con el JavaScript de la página.

**Para solucionar este error de la consola y descartar interferencias:**

1.  **Deshabilita tus extensiones de Chrome:** Intenta deshabilitar todas las extensiones (o al menos las que interactúan con las páginas web, como bloqueadores de anuncios, temas, o herramientas de desarrollo) y luego prueba el modo oscuro de nuevo. Si el error de la consola desaparece y el modo oscuro funciona, podrás ir habilitando las extensiones una a una para encontrar la que causa el conflicto.
2.  **Prueba en modo incógnito:** Las extensiones suelen estar deshabilitadas por defecto en el modo incógnito. Prueba si el problema del modo oscuro persiste ahí.

**Después de intentar lo anterior, por favor, vuelve a realizar las verificaciones que te pedí previamente para el modo oscuro:**

1.  Abre la consola y ejecuta `document.getElementById('theme-toggle-button');`
2.  Ejecuta `document.getElementById('theme-toggle-sun-icon');`
3.  Ejecuta `document.getElementById('theme-toggle-moon-icon');`
4.  Dime los resultados de cada uno de esos comandos y si sigues viendo algún error en la consola (especialmente si el error de la extensión desaparece).

Con esta información, podremos centrarnos en el problema real de tu aplicación si el modo oscuro sigue sin funcionar.
