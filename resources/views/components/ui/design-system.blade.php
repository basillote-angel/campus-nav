{{--
    Design System Constants & Utilities
    Centralized design tokens for consistent styling across the admin dashboard
    
    Usage: Include this file or reference these constants in components
--}}

@php
    // Color Palette - Brand Colors
    $brandPrimary = '#123A7D';      // Primary blue (navistfind blue)
    $brandSecondary = '#3B82F6';     // Secondary blue (blue-500)
    $brandLight = 'rgba(59, 130, 246, 0.08)';  // Light blue background
    $brandText = 'rgba(59, 130, 246, 0.8)';    // Blue text
    
    // Status Colors
    $successBg = '#10B981';          // green-500
    $successText = '#059669';        // green-600
    $warningBg = '#F59E0B';          // amber-500
    $warningText = '#D97706';        // amber-600
    $dangerBg = '#EF4444';           // red-500
    $dangerText = '#DC2626';         // red-600
    $infoBg = '#3B82F6';             // blue-500
    $infoText = '#2563EB';           // blue-600
    
    // Neutral Colors
    $gray50 = '#F9FAFB';
    $gray100 = '#F3F4F6';
    $gray200 = '#E5E7EB';
    $gray300 = '#D1D5DB';
    $gray400 = '#9CA3AF';
    $gray500 = '#6B7280';
    $gray600 = '#4B5563';
    $gray700 = '#374151';
    $gray800 = '#1F2937';
    $gray900 = '#111827';
    
    // Spacing Scale (Tailwind default)
    $spacing = [
        'xs' => '0.25rem',   // 1
        'sm' => '0.5rem',    // 2
        'md' => '1rem',      // 4
        'lg' => '1.5rem',    // 6
        'xl' => '2rem',      // 8
        '2xl' => '3rem',     // 12
        '3xl' => '4rem',     // 16
    ];
    
    // Border Radius
    $radius = [
        'sm' => '0.375rem',   // rounded-lg
        'md' => '0.5rem',      // rounded-xl
        'lg' => '0.75rem',    // rounded-2xl
        'full' => '9999px',   // rounded-full
    ];
    
    // Typography Scale
    $fontSize = [
        'xs' => '0.75rem',    // 12px
        'sm' => '0.875rem',   // 14px
        'base' => '1rem',     // 16px
        'lg' => '1.125rem',   // 18px
        'xl' => '1.25rem',    // 20px
        '2xl' => '1.5rem',    // 24px
        '3xl' => '1.875rem',  // 30px
    ];
    
    // Shadow Presets
    $shadows = [
        'sm' => '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
        'md' => '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
        'lg' => '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
        'xl' => '0 20px 25px -5px rgba(0, 0, 0, 0.1)',
    ];
    
    // Transition Presets
    $transitions = [
        'fast' => '150ms',
        'base' => '300ms',
        'slow' => '500ms',
    ];
@endphp

{{-- This component file is for reference/documentation --}}
{{-- Actual usage: Use Tailwind classes directly or reference these in PHP components --}}

