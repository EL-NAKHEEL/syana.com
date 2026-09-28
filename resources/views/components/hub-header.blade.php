{{-- Section-page header: the owner's heading/intro/photo when the section page is published, else the defaults. --}}
@props(['hub' => null, 'title', 'intro' => null])
<x-page-header :title="$hub?->title ?: $title" :intro="$hub ? $hub->intro : $intro" :media="$hub?->getFirstMedia('header')" />
