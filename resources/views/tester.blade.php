<!doctype html>
    <html lang="{{ app()->getLocale() }}">
    <head>
      <title>Exectute test</title>
      <!-- styling etc. -->
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <form method="POST" action="{{ config('app.url')}}:{{$_SERVER['SERVER_PORT']}}/tester">
                    @csrf <!-- {{ csrf_field() }} -->
                    <h1> Test Laravel tracing</h1>
                    <div class="form-input">
                        <label>Execution endpoint:</label> <input type="text" name="end_url" value="http://localhost:8080/test3" size="100">
                    </div>
                    <p></p>
                    <button type="submit">Execute</button>
                </form>
                    <div class="alert alert-success" role="alert">
                        <p></p>
                        Serving at: {{ config('app.url') }}:{{ $_SERVER['SERVER_PORT'] }}
                    </div>


                    @if (session('message'))
                        <div class="alert alert-success" role="alert">
                            {{ session('message') }}
                        </div>
                    @endif

            </div>
        </div>
    </body>
    </html>