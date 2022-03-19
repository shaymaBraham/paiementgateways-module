@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => ''])
        @if($data['company']['logo'])
            <img class="header-logo" src="{{asset($data['company']['logo'])}}" alt="{{$data['company']['name']}}">
        @else
            {{$data['company']['name']}}
        @endif
        @endcomponent
    @endslot

    {{-- Body --}}
    <!-- Body here -->

    {{-- Subcopy --}}
    @slot('subcopy')
        @component('mail::subcopy')
                {{ __('Vous avez reçu une nouvelle facture de') }} <b>{{$data['company']['name']}}</b>
                <table>
                    <tr>
                        <td class="attribute-label">{{ __('Référence de la facture') }}</td>
                        <td class="attribute-value"> &nbsp;{{$data['invoice']['reference']}}</td>
                    </tr>
                    <tr>
                        <td class="attribute-label">{{ __('Date Facture') }} </td>
                        <td class="attribute-value"> &nbsp;{{$data['invoice']['date']}}</td>
                    </tr>
                </table>
                <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td>
                            @component('mail::button', ['url' => url('/prestataire/facture/pdf/'.$data['invoice']['unique_hash'])])
                                {{ __('Voir la facture') }}
                            @endcomponent
                        </td>
                        <td>
                            @component('mail::button', ['url' => url('/prestataire/facture/payment/'.$data['invoice']['unique_hash']),'color'=>'success'])
                                {{ __('Payer la facture') }}
                            @endcomponent
                        </td>
                    </tr>
                </table>
        @endcomponent
    @endslot
     {{-- Footer --}}
     @slot('footer')
        @component('mail::footer')

        @endcomponent
    @endslot
@endcomponent
