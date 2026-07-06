@extends('layouts.sidebar')

@section('content')

<div class="conatiner-fluid content-inner mt-n5 py-0">
  <div class="row">   
      
   <div class="col-lg-12">
      <div class="card   rounded">
          <div class="card-body">
             <div class="row">
                 <div class="col-sm-12">  

                  @if(session()->has('success'))
                  <div class="alert alert-success">
                      {{ session()->get('success') }}
                  </div>
              @endif
              
              @if(session()->has('error'))
                  <div class="alert alert-danger">
                      {{ session()->get('error') }}
                  </div>
              @endif
              
              @if(($_SERVER['QUERY_STRING'] ?? '') === '')
              <section class="text-gray-400">
                      <h3 class="mb-4 card-header"><i class="bi bi-plug"></i> Integrations</h3>
              <div class="card-body p-0 p-md-3">

                      @foreach($profile as $profile)

              {{-- Email / password / role / delete controls removed —
                   customer auth + account lifecycle are owned by Mail
                   Minted (Supabase SSO + billing). This page holds
                   integrations and data export/import only. --}}

              {{-- ===== Integration cards (each is a partial) ===== --}}
              <div class="col-lg-8">
                <p class="text-muted mb-3">Connect external services to your public page.</p>

                {{-- Stripe connection is account-level (one account serves
                     all payment blocks), so it's managed both here and
                     contextually in the Stripe block — both use the shared
                     stripe-connect-panel partial and stay in sync. --}}
                @include('studio.partials.integration-stripe',    ['profile' => $profile])
                @include('studio.partials.integration-analytics', ['profile' => $profile])
                @include('studio.partials.integration-redirect',  ['profile' => $profile])
              </div>

              @if(env('ALLOW_USER_EXPORT') != false)
              <div class="col-lg-8 mt-4">
                <h4 class="mb-2">{{__('messages.Export user data')}}</h4>
                <p class="text-muted mb-3">{{__('messages.Export your user data')}}</p>
                <div class="row">
                  <div class="col-lg-8">
                    <button class="btn btn-outline-secondary">
                      <a href="{{ route('exportAll') }}">
                        <i class="bi bi-layer-backward"></i> {{__('messages.Export all data')}}
                      </a>
                    </button>
                    <button class="btn btn-outline-secondary">
                      <a href="{{ route('exportLinks') }}">
                        <i class="bi bi-layer-backward"></i> {{__('messages.Export links only')}}
                      </a>
                    </button>
                  </div>
                </div>
              </div>
              @endif
              
              @if(env('ALLOW_USER_IMPORT') != false)
              <form action="{{ route('importData') }}" enctype="multipart/form-data" method="post">
                @csrf
                <div class="form-group col-lg-8 mt-4">
                  <h4 class="mb-2">{{__('messages.Import user data')}}</h4>
                    <label>{{__('messages.Import your user data from another instance')}}</label>
                    <input type="file" accept="application/JSON" class="form-control" id="customFile" name="import">
                </div>
              
                <button type="submit" class="mt-3 ml-3 btn btn-primary" onclick="return confirm('{{__('messages.import.user.alert')}}')">{{__('messages.Import')}}</button>
              </form>
              @endif
              
              {{-- Self-serve "Delete your account" removed: account
                   lifecycle (create / deprovision) is owned by Mail Minted
                   billing, and a local delete here would desync from that
                   and break SSO. Deletion happens via the admin/deprovision
                   API, which also purges uploads. --}}
                        </div>
              </section>
                        @endforeach
              @endif

                 </div>
             </div>
          </div>
       </div>
      </div>
    </div>
  </div>

@endsection
