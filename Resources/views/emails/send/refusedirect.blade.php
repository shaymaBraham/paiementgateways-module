@component('mail::layout')
@slot('header')
&nbsp;
@endslot

    {{-- Body --}}
    <!-- Body here -->

    {{-- Subcopy --}}
    @slot('subcopy')
        @component('mail::subcopy')
            {{ __('Le paiement de votre transaction est refusé') }}:<br>
            @slot('table')

                <tr>
                    <td class="attribute-label">{{ __('Réf.') }}</td>
                    <td class="attribute-value"> &nbsp;{{$data['transaction']['reference']}}</td>
                </tr>
                <tr>
                    <td class="attribute-label">{{ __('Date de la transaction') }}</td>
                    <td class="attribute-value"> &nbsp;{{$data['transaction']['date']}}</td>
                </tr>
                <tr>
                    <td class="attribute-label">{{ __('Libelle') }}</td>
                    <td class="attribute-value"> &nbsp;{{$data['transaction']['libelle']}}</td>
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
