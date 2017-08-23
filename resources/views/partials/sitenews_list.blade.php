        @foreach($news as $item)
            <div class="panel panel-{{ $item->news_type }}">
                <div class="panel-heading ">
                    <h3 class="panel-title">
                        {{ $item->updated_at->format('d/m/Y H:i') }} by {{ $item->user->full_name }} - {{ $item->title }}
                        @if (Auth::user()->has_role('siteadmin')) 
                            <a class="pull-right" href="{!! action('SitenewsController@edit',$item->id) !!}">Edit</a>
                        @endif
                    </h3>
                </div>
                <div class="panel-body">
                    {{ $item->news }}
                </div>
            </div>
        @endforeach
