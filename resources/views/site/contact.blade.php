@extends('site.layout')

@section('title', 'Байланыс — Naryk.kz')

@section('content')
    <nav class="breadcrumbs">
        <a href="/">Басты бет</a>
        <span>/</span>
        <span class="breadcrumbs__current">Байланыс</span>
    </nav>

    <div class="contact">
        <div class="contact__info">
            <h1 class="article__title">Байланыс</h1>

            @if ($description = $settings['site_information']['contactdescription'] ?? null)
                <p class="contact__desc">{{ $description }}</p>
            @endif

            <dl class="contact__list">
                @if ($street = $settings['site_information']['street'] ?? null)
                    <dt>Мекенжай</dt>
                    <dd>{{ $street }}, {{ $settings['site_information']['city'] ?? '' }}</dd>
                @endif
                @if ($phone = $settings['site_information']['sitephone'] ?? null)
                    <dt>Телефон</dt>
                    <dd><a href="tel:{{ preg_replace('/\D+/', '', $phone) }}">{{ $phone }}</a></dd>
                @endif
                @if ($email = $settings['site_information']['siteemail'] ?? null)
                    <dt>E-mail</dt>
                    <dd><a href="mailto:{{ $email }}">{{ $email }}</a></dd>
                @endif
            </dl>
        </div>

        <div class="contact__form-wrap">
            @if (session('sent'))
                <p class="alert alert--success">Хабарламаңыз жіберілді. Рахмет!</p>
            @endif

            <form class="contact__form" method="POST" action="{{ route('contact.store') }}">
                @csrf

                {{-- Honeypot: hidden from people, irresistible to bots. --}}
                <div class="honeypot" aria-hidden="true">
                    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <label class="field">
                    <span class="field__label">Атыңыз</span>
                    <input class="field__input" type="text" name="name" value="{{ old('name') }}" required>
                    @error('name') <span class="field__error">{{ $message }}</span> @enderror
                </label>

                <label class="field">
                    <span class="field__label">E-mail</span>
                    <input class="field__input" type="email" name="email" value="{{ old('email') }}" required>
                    @error('email') <span class="field__error">{{ $message }}</span> @enderror
                </label>

                <label class="field">
                    <span class="field__label">Тақырыбы</span>
                    <input class="field__input" type="text" name="subject" value="{{ old('subject') }}">
                    @error('subject') <span class="field__error">{{ $message }}</span> @enderror
                </label>

                <label class="field">
                    <span class="field__label">Хабарлама</span>
                    <textarea class="field__input" name="message" rows="6" required>{{ old('message') }}</textarea>
                    @error('message') <span class="field__error">{{ $message }}</span> @enderror
                </label>

                <button class="load-more" type="submit">Жіберу</button>
            </form>
        </div>
    </div>
@endsection
