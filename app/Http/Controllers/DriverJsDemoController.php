<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RealRashid\LaravelDriverJs\DriverJs as DriverJsBuilder;
use RealRashid\LaravelDriverJs\Facades\DriverJs;
use RealRashid\LaravelDriverJs\Support\StepHooks;

/**
 * Ejemplo completo de realrashid/laravel-driverjs.
 *
 * REQUISITO: en config/driverjs.php, 'route_middleware' => 'web'.
 * La ruta interna POST /driverjs/tour/completed se registra sin middleware, así
 * que sin el grupo 'web' no hay StartSession y el flag de "tour completado" se
 * pierde en cada petición (el tour se repetiría siempre).
 *
 * IMPORTANTE — el builder es un SINGLETON:
 * `tour()`, `highlight()` y `modal()` hacen reset del estado, y `toJavaScript()`
 * / `render()` lo consumen (vuelven a hacer reset al terminar). Por eso en este
 * controller cada demo:
 *   1. empieza SIEMPRE con tour() / highlight() / modal(), y
 *   2. se renderiza en el acto, en el orden en que la quiero.
 */
class DriverJsDemoController extends Controller
{
    /**
     * Página principal de la demo.
     */
    public function index(): View
    {
        // Cada método devuelve ya el JavaScript final, así que no hay forma de
        // que un tour se "contamine" con los pasos del anterior.
        $scripts = [
            'tourCompleta' => $this->tourCompleta(),
            'highlightBanner' => $this->highlightBanner(),
            'modalBienvenida' => $this->modalBienvenida(),
            'tourConConfirmacion' => $this->tourConConfirmacionAlSalir(),
            'tourHooksPersonalizados' => $this->tourHooksPersonalizados(),
            'tourFluentSteps' => $this->tourFluentSteps(),
            'tourDesdeArray' => $this->tourDesdeArray(),
            'tourAislada' => $this->tourAislada(),
        ];

        // El último tour NO se renderiza aquí: se deja configurado en el
        // singleton para que lo emita la directiva Blade @driverjsTour.
        $this->configurarTourDeBienvenida();

        return view('driverjs.demo', $scripts + [
            // tourCompleted() consulta el almacenamiento (session por defecto)
            // para saber si el usuario ya vio el tour de bienvenida.
            'bienvenidaCompletada' => DriverJs::tourCompleted('demo-bienvenida'),
        ]);
    }

    /**
     * Borra el estado de "tour completado" para poder volver a verlo.
     *
     * resetCompletion() necesita un tour con nombre, por eso empieza por tour().
     */
    public function reiniciar(): RedirectResponse
    {
        DriverJs::tour('demo-bienvenida')->resetCompletion();

        return back();
    }

    /**
     * TOUR 1 — casi todas las opciones globales, hooks y steps posicionados.
     */
    private function tourCompleta(): string
    {
        return DriverJs::tour('demo-tour-completa')
            // ── Configuración global del driver ──────────────────────────────
            ->animate(true)                    // transiciones animadas entre pasos
            ->overlayColor('#0f172a')          // color del overlay (cualquier color CSS)
            ->overlayOpacity(0.75)             // opacidad del overlay (0.0 – 1.0)
            ->smoothScroll(true)               // scroll suave hasta el elemento resaltado
            ->allowClose(true)                 // Escape / clic en el overlay cierran el tour
            ->overlayClickBehavior('nextStep') // 'close' | 'nextStep' | expresión JS
            ->stagePadding(8)                  // separación elemento <-> cutout (px)
            ->stageRadius(12)                  // radio de las esquinas del cutout (px)
            ->allowKeyboardControl(true)       // Escape y flechas del teclado
            ->disableActiveInteraction(false)  // permite interactuar con lo resaltado

            // ── Configuración global del popover ─────────────────────────────
            ->popoverClass('demo-popover')     // clase CSS extra para el popover
            ->popoverOffset(16)                // distancia popover <-> elemento (px)
            ->showProgress()                   // muestra el "X de Y"
            ->progressText('Paso {{current}} de {{total}}')
            ->showButtons(['next', 'previous', 'close'])
            ->disableButtons([])               // botones visibles pero deshabilitados
            ->nextBtnText('Siguiente →')
            ->prevBtnText('← Anterior')
            ->doneBtnText('¡Entendido!')

            // ── Hooks globales ───────────────────────────────────────────────
            // Se pasan como TEXTO: el nombre de una función global o una
            // expresión JS (function / arrow). El paquete los emite sin comillas.
            ->onHighlightStarted('function (element, step, opts) { console.log("[demo] onHighlightStarted", element); }')
            ->onHighlighted(StepHooks::logStep())
            ->onDeselected('function (element, step, opts) { console.log("[demo] onDeselected", element); }')
            ->onPopoverRender('function (popover, opts) { popover.classList.add("is-rendered"); }')
            ->onDestroyStarted('function (element, step, opts) { console.log("[demo] onDestroyStarted"); }')
            ->onDestroyed('function (element, step, opts) { console.log("[demo] onDestroyed"); }')
            // Si sobrescribes onNextClick / onPrevClick / onCloseClick tienes que
            // mover el driver a mano (opts.driver), o el tour se queda quieto.
            ->onNextClick('function (element, step, opts) { opts.driver.moveNext(); }')
            ->onPrevClick('function (element, step, opts) { opts.driver.movePrevious(); }')
            ->onCloseClick('function (element, step, opts) { opts.driver.destroy(); }')

            // ── Steps ─────────────────────────────────────────────────────────
            // step() acepta un array de opciones, pero OJO: solo funcionan las
            // claves que son nombres de método en una sola palabra ('side',
            // 'align', 'element', 'title', 'description'). Para snake_case como
            // 'popover_class' o 'show_progress' hay que usar addStep().
            ->step('#demo-header', '👋 Bienvenido', 'Este tour pasa por casi todas las opciones del paquete.', [
                'side' => 'bottom',
                'align' => 'center',
            ])
            ->step('#demo-kpis', '📊 Métricas', 'Step con position y alineación.', [
                'side' => 'bottom',
                'align' => 'start',
            ])

            // addStep() devuelve el Step, y el Step reenvía al builder los
            // métodos que no son suyos, así que se pueden encadenar pasos.
            ->addStep('#demo-grafico')
            ->title('📈 Gráfico interactivo')
            ->description('Este step deshabilita el botón "anterior" y oculta el progreso.')
            ->top()                        // atajo de ->side('top')
            ->alignEnd()                   // atajo de ->align('end')
            ->popoverClass('demo-popover demo-popover--kpi')
            ->disableButtons(['previous']) // se ve, pero no se puede usar
            ->showProgress(false)
            ->progressText('Este es el paso {{current}} de {{total}}')
            ->onNextClick('function (element, step, opts) { console.log("[demo] siguiente desde el step", opts.state.activeIndex); opts.driver.moveNext(); }')

            ->addStep('#demo-tabla')
            ->title('🗂️ Tabla')
            ->description('Cambia el texto del botón "siguiente" solo en este paso.')
            ->left()
            ->alignStart()
            ->nextBtnText('Ver más →')
            ->onPopoverRender('function (popover, opts) { popover.classList.add("is-rendered"); console.log("[demo] popover listo"); }')

            ->addStep('#demo-form')
            ->title('📝 Formulario')
            ->description('disableActiveInteraction(true) bloquea el elemento mientras está resaltado.')
            ->right()
            ->disableActiveInteraction(true)
            ->doneBtnText('🎉 ¡Listo!')

            // toJavaScript() devuelve el código; toScriptTag() lo envuelve en
            // <script>. En ambos casos el builder se resetea al terminar.
            ->toJavaScript();
    }

    /**
     * HIGHLIGHT — resalta un único elemento, sin botones de navegación ni progreso.
     */
    private function highlightBanner(): string
    {
        return DriverJs::highlight(
            '#demo-banner-upgrade',
            '💎 Plan Pro',
            'Un highlight resalta un único elemento y oculta los botones de navegación y el progreso.'
        )
            ->overlayColor('#1e1b4b')
            ->overlayOpacity(0.65)
            ->stagePadding(12)
            ->stageRadius(18)
            ->popoverClass('demo-popover demo-popover--highlight')
            ->popoverOffset(20)
            ->onDeselected('function (element, step, opts) { console.log("[demo] highlight cerrado"); }')
            ->toJavaScript();
    }

    /**
     * MODAL — popover centrado, sin elemento que resaltar.
     */
    private function modalBienvenida(): string
    {
        return DriverJs::modal(
            '🎉 Modal sin elemento',
            'Los modales no tienen target: el popover aparece centrado en la pantalla, '
            .'sin flecha y sin recortar nada del fondo.'
        )
            ->overlayColor('#111827')
            ->overlayOpacity(0.85)
            ->showButtons(['close'])
            ->popoverClass('demo-popover demo-popover--modal')
            ->popoverOffset(0)
            ->onHighlighted('function (element, step, opts) { console.log("[demo] modal visible"); }')
            ->toJavaScript();
    }

    /**
     * TOUR con confirmación antes de cerrar (StepHooks).
     *
     * preventDestroy() haría lo mismo pero sin dejar salida: útil para tours
     * obligatorios de onboarding.
     */
    private function tourConConfirmacionAlSalir(): string
    {
        return DriverJs::tour('demo-confirmacion')
            ->allowClose(true)
            ->showProgress()
            ->progressText('Paso {{current}} de {{total}}')
            ->doneBtnText('Salir del tour')
            ->onDestroyStarted(StepHooks::confirmBeforeDestroy('¿Seguro que quieres salir del tour antes de terminarlo?'))
            ->onDestroyed('function (element, step, opts) { console.log("[demo] tour cerrado con confirmación"); }')
            ->step('#demo-header', 'Tour con confirmación', 'Intenta salir con Escape, con la X o clicando el overlay.')
            ->step('#demo-kpis', 'Vas por la mitad', 'Mientras no salgas, el tour sigue abierto.')
            ->step('#demo-form', 'Último paso', 'Ahora sí, pulsa "Salir del tour".')
            ->toJavaScript();
    }

    /**
     * TOUR con hooks por step y validación en onNextClick.
     */
    private function tourHooksPersonalizados(): string
    {
        return DriverJs::tour('demo-hooks')
            ->showProgress()
            ->progressText('{{current}} / {{total}}')
            ->nextBtnText('Continuar →')
            ->prevBtnText('← Volver')
            ->onHighlighted(StepHooks::logStep())
            // Hook a nivel de tour: se ejecuta en CADA step.
            ->onPopoverRender('function (popover, opts) { popover.dataset.step = opts.state.activeIndex; }')
            ->step('#demo-grafico', '1. Gráfico', 'Los hooks globales se disparan en cada paso.')
            ->addStep('#demo-input')
            ->title('2. Validación en onNextClick')
            ->description('Este onNextClick no hace moveNext() hasta que marques la casilla.')
                // El popover se ancla al checkbox, así que se puede interactuar
                // con él: disableActiveInteraction viene en false por defecto.
            ->onNextClick(<<<'JS'
                    function (element, step, opts) {
                        var casilla = document.getElementById('demo-input-check');
                        if (casilla.checked) {
                            opts.driver.moveNext();
                        } else {
                            console.log('[demo] bloqueado: marca la casilla para continuar');
                        }
                    }
                    JS)
            ->addStep('#demo-footer')
            ->title('3. Listo')
            ->description('El último hook del tour se ejecuta al destruirse.')
            ->onDestroyed('function (element, step, opts) { console.log("[demo] tour de hooks terminado"); }')
            ->toJavaScript();
    }

    /**
     * TOUR con la API fluida de Step y un target definido como función JS.
     *
     * Si el valor de element empieza por "function", "(" o "async " el paquete
     * lo emite como JavaScript crudo en lugar de como string.
     */
    private function tourFluentSteps(): string
    {
        return DriverJs::tour('demo-fluent')
            ->showProgress()
            ->progressText('{{current}} de {{total}}')
            ->popoverClass('demo-popover')
            ->addStep('#demo-boton-avanzado')
            ->title('🎯 Target dinámico')
            ->description('El elemento se resuelve con una función JS en el momento de mostrarse.')
            ->over()                        // sin elemento -> popover centrado
            ->showProgress(false)
            ->disableButtons(['previous'])
            ->nextBtnText('Ya lo vi')
            ->addStep('() => document.querySelector("#demo-boton-avanzado")')
            ->title('🔎 Mismo botón, por función')
            ->description('Driver.js evaluates el elemento en cada paso, así que funciona aunque el DOM cambie.')
            ->bottom()
            ->alignCenter()
            ->doneBtnText('Perfecto')
            ->toJavaScript();
    }

    /**
     * TOUR declarado como array + arranque en un paso concreto.
     *
     * toJavaScript(1) genera driverInstance.drive(1): empieza en el segundo
     * paso en lugar del primero.
     */
    private function tourDesdeArray(): string
    {
        return DriverJs::tour('demo-array')
            ->showProgress()
            ->progressText('Paso {{current}} de {{total}}')
            ->addSteps([
                [
                    'element' => '#demo-lista',
                    'title' => '1. Lista',
                    'description' => 'addSteps() acepta un array asociativo por paso.',
                    'side' => 'right',
                    'align' => 'start',
                ],
                [
                    'element' => '#demo-tabla',
                    'title' => '2. Tabla',
                    'description' => 'Este paso oculta el progreso solo para él.',
                    'side' => 'left',
                    'align' => 'end',
                    'show_progress' => false,
                ],
                [
                    'element' => '#demo-footer',
                    'title' => '3. Pie de página',
                    'description' => 'El último paso puede cambiar el texto del botón "Done".',
                    'popover_class' => 'demo-popover demo-popover--modal',
                    'done_btn_text' => 'Cerrar demo',
                ],
            ])
            ->toJavaScript(1); // arranca en el paso 2
    }

    /**
     * Instancia aislada con DriverJs::make().
     *
     * make() devuelve un builder NUEVO, sin tocar el singleton: útil cuando
     * en una misma petición compones varios tours y no quieres depender del
     * reset de cada cadena.
     */
    private function tourAislada(): string
    {
        return DriverJsBuilder::make()
            ->tour('demo-aislada')
            ->showProgress()
            ->progressText('Aislada: {{current}} de {{total}}')
            // Los textos por defecto de config/driverjs.php vienen como entidades
            // HTML ('Next &rarr;'). Se sobrescriben aquí con caracteres reales.
            ->nextBtnText('Siguiente →')
            ->prevBtnText('← Anterior')
            ->doneBtnText('Hecho')
            ->step('#demo-banner-upgrade', 'Builder independiente', 'Generado con DriverJs::make(), no con el singleton.')
            ->step('#demo-lista', 'Sin efectos colaterales', 'Esta cadena no ha podido contaminar ningún otro tour.')
            ->toScriptTag(); // devuelve el <script>...</script> completo
    }

    /**
     * Tour de bienvenida: se deja CONFIGURADO para @driverjsTour.
     *
     * El directive lee el estado que haya en el singleton en ese momento y, si
     * el tour ya está marcado como completado, no imprime nada.
     */
    private function configurarTourDeBienvenida(): void
    {
        DriverJs::tour('demo-bienvenida')
            ->showProgress()
            ->progressText('Paso {{current}} de {{total}}')
            ->nextBtnText('Vamos →')
            ->doneBtnText('Entendido')
            ->onDestroyed('function (element, step, opts) { console.log("[demo] tour de bienvenida completado"); }')
            ->step('#demo-header', 'Tour automático', 'Este lo imprime la directiva @driverjsTour al cargar la página.')
            ->step('#demo-kpis', 'Solo la primera vez', 'Al terminar se guarda en la sesión y no vuelve a mostrarse.')
            ->step('#demo-footer', 'Reiniciable', 'Usa el botón "Reiniciar tour" para volver a verlo.');
    }
}
