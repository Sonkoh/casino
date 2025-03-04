@extends('base')
@section('content')
<div class="container">
    <div id="lost_connection_alert" style="display: none;">
        <div class="alert alert-dismissible bg-light-danger d-flex flex-column flex-sm-row p-5 mb-10">
            <span class="spinner-border text-danger me-4 my-auto">
                <span class="visually-hidden">Conectandose al servidor</span>
            </span>
            <div class="d-flex flex-column pe-0 pe-sm-10 my-auto">
                <h4 class="fw-semibold m-0" id="lost_connection_alert_title"></h4>
                <span id="lost_connection_alert_description"></span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card p-5 mb-5">
                <h3 class="m-0">Mesas de Poker</h3>
                <a href="m-0 text-muted">Crear Mesa</a>
            </div>
            <div id="poker_tables">
                <div class="card p-5">
                    <div class="d-flex gap-4 mb-4">
                        <div class="rounded-4 bg-light w-75px h-75px d-flex justify-content-center">
                            <div class="d-flex align-items-center"><img src="/img/pieces.png" class="w-50px"></div>
                        </div>
                        <div class="d-flex justify-content-center flex-column">
                            <h3 class="mb-2">Mesa de Sonkoh</h3>
                            <div class="symbol-group symbol-hover">
                                <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" aria-label="Barry Walter" data-bs-original-title="Barry Walter" data-kt-initialized="1">
                                    <img alt="Pic" src="/metronic8/demo2/assets/media/avatars/300-12.jpg">
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-danger">Unirse</button>
                </div>
            </div>
        </div>
        <div class="col-md-6">

        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    class ReconnectingWebSocket {
        constructor(url, maxRetries = 100, retryDelay = 1000) {
            this.url = url;
            this.maxRetries = maxRetries;
            this.retryDelay = retryDelay;
            this.retries = 0;
            this.connect();
        }

        connect() {
            console.log('Conectando a WebSocket...');
            this.socket = new WebSocket(this.url);

            this.socket.addEventListener('open', () => {
                $("#lost_connection_alert").fadeOut(500);
                this.retries = 0;
                $.ajax({
                    type: "GET",
                    url: "/api/get_access_token",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: (response) => {
                        if (response.success) {
                            this.socket.send(JSON.stringify({
                                request: "auth.login",
                                data: response.response
                            }));
                        }
                    }
                });
            });

            this.socket.addEventListener('message', (event) => {
                let response = JSON.parse(event.data);
                if (response.success && response.response == "auth.login")
                    this.socket.send(JSON.stringify({
                        request: "poker.get_tables"
                    }));
                console.log('Mensaje recibido:', JSON.parse(event.data));
            });

            this.socket.addEventListener('close', () => {
                $("#lost_connection_alert_title").html("Reintentando conexión");
                $("#lost_connection_alert_description").html("Se perdió la conexión por unos segundos, reintentando conexión.");
                $("#lost_connection_alert").fadeIn(500);
                this.reconnect();
            });

            this.socket.addEventListener('error', (event) => {
                $("#lost_connection_alert_title").html("Ha ocurrido un error");
                $("#lost_connection_alert_description").html("Error en WebSocket, reintentando...");
                $("#lost_connection_alert").fadeIn(500);
                setTimeout(() => {
                    $("#lost_connection_alert").fadeOut(500);
                }, 10000);
                this.socket.close();
            });
        }

        reconnect() {
            if (this.retries < this.maxRetries) {
                const delay = this.retryDelay * Math.pow(2, this.retries); // Retraso exponencial
                console.log(`Intentando reconectar en ${delay / 1000} segundos...`);
                setTimeout(() => {
                    this.retries++;
                    this.connect();
                }, delay);
            } else {
                console.error('Se alcanzó el máximo de intentos de reconexión');
                $("#lost_connection_alert_title").html("No se pudo reconectar");
                $("#lost_connection_alert_description").html("Verifica tu conexión e intenta recargar la página.");
            }
        }

        send(message) {
            if (this.socket.readyState === WebSocket.OPEN) {
                this.socket.send(message);
            } else {
                console.warn('No se pudo enviar, el WebSocket no está conectado');
            }
        }
    }

    const ws = new ReconnectingWebSocket('ws://127.0.0.1:8080');
</script>
@endsection