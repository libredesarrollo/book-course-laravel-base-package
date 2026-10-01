<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Demo · Laravel Driver.js</title>

    {{--
        1) ASSETS DE DRIVER.JS
        -----------------------
        @driverjsAssets emite el <link> de driver.css y el <script> de driver.js
        desde el CDN de jsDelivr. Solo imprime algo cuando
        config('driverjs.asset_source') === 'cdn'.

        Si prefieres compilarlo con Vite, instala driver.js por npm, pon
        'asset_source' => 'npm' en config/driverjs.php e importa tú el CSS y el JS:
        esta directiva no imprimirá nada.
    --}}
    @driverjsAssets

    {{-- CSS propio de la demo (sin dependencias para que la página funcione sola). --}}
    <style>
        :root {
            --bg: #0b1120;
            --panel: #111a2e;
            --panel-2: #16213a;
            --line: #24304d;
            --text: #e6edf8;
            --muted: #8fa0bf;
            --brand: #6366f1;
            --brand-2: #22d3ee;
            --ok: #34d399;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: radial-gradient(1200px 600px at 20% -10%, #1b2650 0%, var(--bg) 60%);
            color: var(--text);
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            line-height: 1.55;
        }

        .wrap { max-width: 1080px; margin: 0 auto; padding: 28px 20px 80px; }

        /* ---------- Cabecera ---------- */
        .topbar {
            display: flex; flex-wrap: wrap; gap: 16px;
            align-items: center; justify-content: space-between;
            padding: 18px 22px;
            background: linear-gradient(120deg, var(--panel), var(--panel-2));
            border: 1px solid var(--line);
            border-radius: 16px;
        }
        .brand { display: flex; align-items: center; gap: 12px; }
        .logo {
            width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center;
            background: linear-gradient(135deg, var(--brand), var(--brand-2)); font-size: 20px;
        }
        .topbar h1 { font-size: 18px; margin: 0; }
        .topbar p { margin: 0; font-size: 13px; color: var(--muted); }

        /* ---------- Bloque de demos ---------- */
        .panel {
            background: var(--panel); border: 1px solid var(--line);
            border-radius: 16px; padding: 22px; margin-top: 22px;
        }
        .panel > h2 { margin: 0 0 6px; font-size: 17px; }
        .panel > p.hint { margin: 0 0 16px; color: var(--muted); font-size: 14px; }

        .grid { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }

        button.btn {
            font: inherit; cursor: pointer; text-align: left;
            background: var(--panel-2); color: var(--text);
            border: 1px solid var(--line); border-radius: 12px;
            padding: 12px 14px; transition: border-color .15s, transform .15s, background .15s;
        }
        button.btn:hover { border-color: var(--brand); transform: translateY(-1px); }
        button.btn strong { display: block; font-size: 14px; }
        button.btn small { color: var(--muted); font-size: 12px; }
        button.btn.primary { background: linear-gradient(135deg, var(--brand), #4f46e5); border-color: transparent; }

        /* ---------- KPIs ---------- */
        .kpis { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
        .kpi { background: var(--panel-2); border: 1px solid var(--line); border-radius: 14px; padding: 16px; }
        .kpi .label { font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; }
        .kpi .value { font-size: 26px; font-weight: 700; margin-top: 4px; }
        .kpi .delta { font-size: 12px; color: var(--ok); }

        /* ---------- Gráfico (CSS puro) ---------- */
        .chart { display: flex; align-items: flex-end; gap: 10px; height: 160px; padding: 12px; }
        .chart div {
            flex: 1; border-radius: 8px 8px 4px 4px;
            background: linear-gradient(180deg, var(--brand-2), var(--brand));
            opacity: .85;
        }

        /* ---------- Tabla ---------- */
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { text-align: left; padding: 10px 12px; border-bottom: 1px solid var(--line); }
        th { color: var(--muted); font-size: 12px; text-transform: uppercase; letter-spacing: .06em; }
        tr:last-child td { border-bottom: 0; }
        .pill { font-size: 12px; padding: 2px 8px; border-radius: 999px; background: #1e293b; border: 1px solid var(--line); }

        /* ---------- Formulario ---------- */
        .field { margin-bottom: 14px; }
        .field label { display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px; }
        input[type="text"], input[type="email"] {
            font: inherit; width: 100%; color: var(--text); background: #0d1425;
            border: 1px solid var(--line); border-radius: 10px; padding: 10px 12px;
        }
        .check { display: flex; align-items: center; gap: 10px; font-size: 14px; }

        /* ---------- Banner ---------- */
        .banner {
            margin-top: 22px; padding: 20px 22px; border-radius: 16px;
            background: linear-gradient(120deg, #312e81, #0e7490);
            border: 1px solid #4f46e5;
        }
        .banner h3 { margin: 0 0 4px; }

        /* ---------- Lista ---------- */
        .list { margin: 0; padding-left: 18px; color: var(--muted); }
        .list li { margin-bottom: 4px; }

        /* ---------- Footer ---------- */
        .footer { margin-top: 30px; padding: 20px 22px; border-top: 1px solid var(--line); color: var(--muted); font-size: 13px; }

        code {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12.5px;
            background: #0d1425; border: 1px solid var(--line); border-radius: 6px; padding: 1px 6px;
        }

        /* ---------- Personalización del popover de Driver.js ---------- */
        /* La clase se define con ->popoverClass('demo-popover') o por step. */
        .demo-popover {
            background: linear-gradient(135deg, #1b2450, #101a35) !important;
            border: 1px solid #3b4a7d !important;
            color: var(--text) !important;
            border-radius: 16px !important;
            box-shadow: 0 24px 60px rgba(0, 0, 0, .45) !important;
            font-family: inherit !important;
        }
        .demo-popover .driver-popover-title { font-weight: 700; }
        .demo-popover .driver-popover-description { color: #c3cfe6; line-height: 1.5; }
        .demo-popover .driver-popover-footer button {
            background: linear-gradient(135deg, var(--brand), #4f46e5);
            border: 0; border-radius: 9px; color: #fff; padding: 7px 13px; font: inherit; cursor: pointer;
        }
        .demo-popover .driver-popover-footer button:hover { filter: brightness(1.12); }
        /* Marca visual que añade el hook onPopoverRender */
        .demo-popover.is-rendered { outline: 1px solid var(--brand-2); }
        .demo-popover--highlight { border-color: var(--brand-2) !important; }
        .demo-popover--modal { text-align: center; min-width: 320px; }
        .demo-popover--kpi { border-color: var(--ok) !important; }
    </style>
</head>

<body>
    <div class="wrap">

        {{-- =================================================================== --}}
        {{-- ELEMENTO 1 · #demo-header (primer paso del tour de bienvenida)    --}}
        {{-- =================================================================== --}}
        <header class="topbar" id="demo-header">
            <div class="brand">
                <div class="logo">🧭</div>
                <div>
                    <h1>Laravel Driver.js</h1>
                    <p>Demo de tours, highlights y modales desde PHP</p>
                </div>
            </div>

            <div class="brand">
                @if ($bienvenidaCompletada)
                    <span class="pill" style="color: var(--ok)">✔ Tour de bienvenida ya completado</span>
                @else
                    <span class="pill">Tour de bienvenida pendiente</span>
                @endif

                {{-- Reset del estado de "completado" (DriverJs::tour()->resetCompletion()). --}}
                <form method="POST" action="{{ route('driverjs-demo.reiniciar') }}">
                    @csrf
                    <button type="submit" class="btn" style="padding: 8px 12px">
                        <strong>Reiniciar tour</strong>
                    </button>
                </form>
            </div>
        </header>

        {{-- =================================================================== --}}
        {{-- PANEL DE BOTONES · cada uno arranca una demo construida en PHP      --}}
        {{-- =================================================================== --}}
        <section class="panel">
            <h2>Demostraciones</h2>
            <p class="hint">
                Todo este JavaScript está generado por el paquete en el controller. Abre la consola
                del navegador: verás los <code>console.log</code> de los hooks.
            </p>

            <div class="grid">
                <button class="btn primary" data-demo="tourCompleta">
                    <strong>1 · Tour completo</strong>
                    <small>Opciones globales, hooks, steps y posicionado</small>
                </button>

                <button class="btn" data-demo="highlightBanner">
                    <strong>2 · Highlight</strong>
                    <small>Resalta #demo-banner-upgrade sin botones</small>
                </button>

                <button class="btn" data-demo="modalBienvenida">
                    <strong>3 · Modal</strong>
                    <small>Popover centrado, sin elemento</small>
                </button>

                <button class="btn" data-demo="tourConConfirmacion">
                    <strong>4 · Confirmar al salir</strong>
                    <small>onDestroyStarted con confirm()</small>
                </button>

                <button class="btn" data-demo="tourHooksPersonalizados">
                    <strong>5 · Hooks por step</strong>
                    <small>onNextClick con validación real</small>
                </button>

                <button class="btn" data-demo="tourFluentSteps">
                    <strong>6 · Steps fluidos</strong>
                    <small>over(), target como función JS</small>
                </button>

                <button class="btn" data-demo="tourDesdeArray">
                    <strong>7 · Tour desde array</strong>
                    <small>addSteps() y arranque en el paso 2</small>
                </button>

                <button class="btn" data-demo="tourAislada">
                    <strong>8 · Builder aislado</strong>
                    <small>DriverJs::make() sin singleton</small>
                </button>
            </div>

            {{-- Instancia global que expone el paquete para controlarla por JS. --}}
            <div class="grid" style="margin-top: 12px">
                <button class="btn" id="btn-mover">
                    <strong>9 · Control por JavaScript</strong>
                    <small>window.driverInstance.moveNext() / movePrevious() / destroy()</small>
                </button>
            </div>
        </section>

        {{-- =================================================================== --}}
        {{-- ELEMENTO 2 · #demo-kpis                                              --}}
        {{-- =================================================================== --}}
        <section class="panel" id="demo-kpis">
            <h2>Métricas del mes</h2>
            <p class="hint">Segundo paso del tour: se ancla abajo y alineado al inicio.</p>

            <div class="kpis">
                <div class="kpi">
                    <div class="label">Usuarios</div>
                    <div class="value">12.480</div>
                    <div class="delta">▲ 8,2 %</div>
                </div>
                <div class="kpi">
                    <div class="label">Ingresos</div>
                    <div class="value">€48.930</div>
                    <div class="delta">▲ 3,1 %</div>
                </div>
                <div class="kpi">
                    <div class="label">Conversión</div>
                    <div class="value">4,7 %</div>
                    <div class="delta">▲ 0,4 %</div>
                </div>
            </div>
        </section>

        {{-- =================================================================== --}}
        {{-- ELEMENTO 3 · #demo-grafico                                           --}}
        {{-- =================================================================== --}}
        <section class="panel" id="demo-grafico">
            <h2>Tráfico semanal</h2>
            <p class="hint">Tercer paso del tour completo: aparece ARRIBA y alineado al final.</p>

            <div class="chart">
                <div style="height: 38%"></div>
                <div style="height: 62%"></div>
                <div style="height: 45%"></div>
                <div style="height: 80%"></div>
                <div style="height: 55%"></div>
                <div style="height: 92%"></div>
                <div style="height: 70%"></div>
            </div>
        </section>

        {{-- =================================================================== --}}
        {{-- ELEMENTO 4 · #demo-tabla                                             --}}
        {{-- =================================================================== --}}
        <section class="panel" id="demo-tabla">
            <h2>Pedidos recientes</h2>
            <p class="hint">Aparece a la IZQUIERDA en el tour completo y en el tour de hooks.</p>

            <table>
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#4821</td>
                        <td>Desarrollo Libre</td>
                        <td>€249,00</td>
                        <td><span class="pill">Pagado</span></td>
                    </tr>
                    <tr>
                        <td>#4820</td>
                        <td>Ana Ruiz</td>
                        <td>€89,00</td>
                        <td><span class="pill">Enviado</span></td>
                    </tr>
                    <tr>
                        <td>#4819</td>
                        <td>Carlos Díaz</td>
                        <td>€1.490,00</td>
                        <td><span class="pill">Pendiente</span></td>
                    </tr>
                </tbody>
            </table>
        </section>

        {{-- =================================================================== --}}
        {{-- ELEMENTO 5 · #demo-form                                              --}}
        {{-- =================================================================== --}}
        <section class="panel" id="demo-form">
            <h2>Facturación</h2>
            <p class="hint">
                En el tour completo este step usa <code>disableActiveInteraction(true)</code>:
                no podrás hacer clic aquí hasta que el tour avance.
            </p>

            <div class="field">
                <label for="demo-nombre">Titular</label>
                <input type="text" id="demo-nombre" placeholder="Nombre y apellidos">
            </div>

            <div class="field">
                <label for="demo-email">Email</label>
                <input type="email" id="demo-email" placeholder="hola@ejemplo.com">
            </div>

            {{--
                ELEMENTO 6 · #demo-input
                Este checkbox SÍ es interactivo durante el tour de hooks, porque
                el popover se ancla a él y disableActiveInteraction está en false.
            --}}
            <div class="check" id="demo-input">
                <input type="checkbox" id="demo-input-check">
                <label for="demo-input-check" style="margin:0; color: var(--text)">
                    He leído los términos (necesario para avanzar en el tour de hooks)
                </label>
            </div>
        </section>

        {{-- =================================================================== --}}
        {{-- ELEMENTO 7 · #demo-banner-upgrade (target del highlight)            --}}
        {{-- =================================================================== --}}
        <section class="banner" id="demo-banner-upgrade">
            <h3>💎 Actualiza a Plan Pro</h3>
            <p style="margin:0; color:#dbeafe">
                Target del <strong>highlight</strong> (demo 2) y del tour aislado (demo 8).
            </p>
        </section>

        {{-- =================================================================== --}}
        {{-- ELEMENTO 8 · #demo-lista                                             --}}
        {{-- =================================================================== --}}
        <section class="panel" id="demo-lista">
            <h2>Checklist de onboarding</h2>
            <ul class="list">
                <li>Completa el perfil de la empresa</li>
                <li>Invita a tu equipo</li>
                <li>Configura la pasarela de pago</li>
                <li>Conecta el canal de soporte</li>
            </ul>
        </section>

        {{-- =================================================================== --}}
        {{-- ELEMENTO 9 · #demo-boton-avanzado                                   --}}
        {{-- =================================================================== --}}
        <section class="panel">
            <h2>Elemento resaltable</h2>
            <p class="hint">
                El tour 6 apunta a este botón dos veces: una con selector CSS y otra
                con <code>() =&gt; document.querySelector('#demo-boton-avanzado')</code>.
            </p>
            <button class="btn primary" id="demo-boton-avanzado" style="display:inline-block">
                <strong>Acción primaria</strong>
            </button>
        </section>

        {{-- =================================================================== --}}
        {{-- ELEMENTO 10 · #demo-footer                                          --}}
        {{-- =================================================================== --}}
        <footer class="footer" id="demo-footer">
            Realrashid/laravel-driverjs · <code>DriverJs::tour()</code> · <code>DriverJs::highlight()</code>
            · <code>DriverJs::modal()</code> · <code>@@driverjsAssets</code> · <code>@@driverjsTour</code>
        </footer>
    </div>

    {{-- ======================================================================= --}}
    {{-- 2) LOS TOURS DEFINIDOS CON LAS DIRECTIVAS BLADE                         --}}
    {{-- ======================================================================= --}}

    {{--
        @driverjsTour('nombre') imprime el tour que esté configurado en el
        singleton en ese momento, SOLO si no está marcado como completado.

        Por eso el tour de bienvenida se construye en
        DriverJsDemoController::configurarTourDeBienvenida() (la última cosa que
        hace el controller) y aquí solo se emite la directiva.

        OJO: esta directiva llama a render(), que con asset_source = 'cdn'
        vuelve a imprimir los tags de driver.js. Como ya los impresión
        @driverjsAssets en <head>, el <script> aparece dos veces. Es inocuo
        (el navegador cachea y la librería solo se reasigna), pero en un
        proyecto real usa ->toJavaScript() si ya emitiste @driverjsAssets.
    --}}
    @driverjsTour('demo-bienvenida')

    {{-- 3) HIGHLIGHT DEFINIDO CON DIRECTIVA --}}
    {{--
        @driverjsHighlight('#selector', 'Título', 'Descripción') también arranca
        solo. Se deja comentado para que no se solape con el tour de bienvenida:
        actívalo con esta línea si quieres probarlo.

        @driverjsHighlight('#demo-grafico', 'Gráfico', 'Esto es un highlight directo.')
    --}}

    {{-- ======================================================================= --}}
    {{-- 4) ARRANQUE MANUAL DE LAS DEMOS                                         --}}
    {{-- ======================================================================= --}}
    {{--
        El JavaScript que genera el paquete SIEMPRE arranca la demo (llama a
        drive() o highlight()). Para que cada botón la ejecute solo al hacer
        clic, envolvemos el código generado dentro de una función.

        Cada variable ($tourCompleta, $highlightBanner, ...) es el string que
        devuelve ->toJavaScript() / ->toScriptTag() en el controller.
    --}}

    {{--
        La demo 8 viene de ->toScriptTag(), o sea que ya es un <script> completo.
        Va dentro de un <template> porque un <script> anidado en otro <script>
        cerraría el primero: el parser HTML corta el elemento en el primer
        </script> que encuentra. El contenido de un <template> es inerte, así que
        es el sitio correcto para guardar código que quieras lanzar después.
    --}}
    <template id="demo-tour-aislada">{!! $tourAislada !!}</template>

    <script>
        window.demoDriverJs = {
            tourCompleta: function () {
                {!! $tourCompleta !!}
            },
            highlightBanner: function () {
                {!! $highlightBanner !!}
            },
            modalBienvenida: function () {
                {!! $modalBienvenida !!}
            },
            tourConConfirmacion: function () {
                {!! $tourConConfirmacion !!}
            },
            tourHooksPersonalizados: function () {
                {!! $tourHooksPersonalizados !!}
            },
            tourFluentSteps: function () {
                {!! $tourFluentSteps !!}
            },
            tourDesdeArray: function () {
                {!! $tourDesdeArray !!}
            },
            // Esta viene de toScriptTag(): ya es un elemento script completo,
            // guardado dentro del template de arriba. Ojo: aquí NO se puede
            // escribir la etiqueta de cierre en un comentario, porque el parser
            // HTML corta el bloque en cuanto la encuentra.
            tourAislada: function () {
                var original = document.getElementById('demo-tour-aislada').content.querySelector('script');

                var nuevo = document.createElement('script');
                nuevo.textContent = original.textContent;
                document.body.appendChild(nuevo);
            }
        };

        // Botones de la demo: delegan en el objeto de arriba.
        document.querySelectorAll('[data-demo]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var demo = window.demoDriverJs[boton.dataset.demo];
                if (typeof demo === 'function') {
                    demo();
                }
            });
        });

        // Control programático: el paquete deja la instancia en window.
        // - window.driverInstance          → siempre
        // - window.driverInstances['name'] → solo en tours con nombre
        document.getElementById('btn-mover').addEventListener('click', function () {
            var d = window.driverInstance;
            if (!d) {
                console.log('[demo] todavía no hay ningún tour abierto');
                return;
            }
            console.log('[demo] paso activo:', d.getConfig('activeIndex'));
        });
    </script>
</body>

</html>