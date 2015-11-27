        <div class="page-header">
            <h1>
                <i>{{ $helloWords[array_rand($helloWords)] }}</i>
                {{ Auth::user()->fullName() }}
            </h1>
        </div>
