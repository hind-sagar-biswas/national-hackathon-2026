
import { definePreset } from '@primeuix/themes';
import Aura from '@primeuix/themes/aura';

const DaisyPrimePreset = definePreset(Aura, {
  semantic: {
    primary: {
      50:  'color-mix(in oklch, var(--color-primary) 10%, var(--color-base-100))',
      100: 'color-mix(in oklch, var(--color-primary) 20%, var(--color-base-100))',
      200: 'color-mix(in oklch, var(--color-primary) 35%, var(--color-base-100))',
      300: 'color-mix(in oklch, var(--color-primary) 50%, var(--color-base-100))',
      400: 'color-mix(in oklch, var(--color-primary) 70%, var(--color-base-100))',
      500: 'var(--color-primary)',
      600: 'color-mix(in oklch, var(--color-primary) 85%, black)',
      700: 'color-mix(in oklch, var(--color-primary) 70%, black)',
      800: 'color-mix(in oklch, var(--color-primary) 55%, black)',
      900: 'color-mix(in oklch, var(--color-primary) 40%, black)',
      950: 'color-mix(in oklch, var(--color-primary) 25%, black)',
    },
    colorScheme: {
      light: {
        surface: {
          0:   'var(--color-base-100)',
          50:  'color-mix(in oklch, var(--color-base-100) 80%, var(--color-base-200))',
          100: 'var(--color-base-200)',
          200: 'var(--color-base-300)',
          300: 'color-mix(in oklch, var(--color-base-300) 70%, var(--color-base-content))',
          400: 'color-mix(in oklch, var(--color-base-300) 50%, var(--color-base-content))',
          500: 'color-mix(in oklch, var(--color-base-300) 35%, var(--color-base-content))',
          600: 'color-mix(in oklch, var(--color-base-300) 20%, var(--color-base-content))',
          700: 'color-mix(in oklch, var(--color-base-100) 15%, var(--color-base-content))',
          800: 'color-mix(in oklch, var(--color-base-100) 10%, var(--color-base-content))',
          900: 'color-mix(in oklch, var(--color-base-100) 5%,  var(--color-base-content))',
          950: 'var(--color-base-content)',
        },
        primary: {
          color:          '{primary.500}',
          contrastColor:  'var(--color-primary-content)',
          hoverColor:     '{primary.600}',
          activeColor:    '{primary.700}',
        },
        highlight: {
          background:        '{primary.50}',
          focusBackground:   '{primary.100}',
          color:             '{primary.700}',
          focusColor:        '{primary.800}',
        },
        text: {
          color:              'var(--color-base-content)',
          hoverColor:         'var(--color-base-content)',
          mutedColor:         'color-mix(in oklch, var(--color-base-content) 55%, var(--color-base-100))',
          hoverMutedColor:    'color-mix(in oklch, var(--color-base-content) 70%, var(--color-base-100))',
        },
        content: {
          background:         'var(--color-base-100)',
          hoverBackground:    'var(--color-base-200)',
          borderColor:        'var(--color-base-300)',
          color:              'var(--color-base-content)',
          hoverColor:         'var(--color-base-content)',
        },
        overlay: {
          select: {
            background:   'var(--color-base-100)',
            borderColor:  'var(--color-base-300)',
            color:        'var(--color-base-content)',
          },
          popover: {
            background:   'var(--color-base-100)',
            borderColor:  'var(--color-base-300)',
            color:        'var(--color-base-content)',
          },
          modal: {
            background:   'var(--color-base-100)',
            borderColor:  'var(--color-base-300)',
            color:        'var(--color-base-content)',
          },
        },
        formField: {
          background:         'var(--color-base-100)',
          disabledBackground: 'var(--color-base-200)',
          filledBackground:   'var(--color-base-200)',
          borderColor:        'var(--color-base-300)',
          hoverBorderColor:   'var(--color-primary)',
          focusBorderColor:   'var(--color-primary)',
          color:              'var(--color-base-content)',
          placeholderColor:   'color-mix(in oklch, var(--color-base-content) 50%, var(--color-base-100))',
        },
        list: {
          option: {
            focusBackground:          'var(--color-base-200)',
            selectedBackground:       '{primary.50}',
            selectedFocusBackground:  '{primary.100}',
            color:                    'var(--color-base-content)',
            focusColor:               'var(--color-base-content)',
            selectedColor:            '{primary.700}',
            selectedFocusColor:       '{primary.800}',
          },
        },
      },

      // Dark mode — daisyUI swaps its own vars, so just mirror light
      dark: {
        surface: {
          0:   'var(--color-base-100)',
          50:  'color-mix(in oklch, var(--color-base-100) 80%, var(--color-base-200))',
          100: 'var(--color-base-200)',
          200: 'var(--color-base-300)',
          300: 'color-mix(in oklch, var(--color-base-300) 70%, var(--color-base-content))',
          400: 'color-mix(in oklch, var(--color-base-300) 50%, var(--color-base-content))',
          500: 'color-mix(in oklch, var(--color-base-300) 35%, var(--color-base-content))',
          600: 'color-mix(in oklch, var(--color-base-300) 20%, var(--color-base-content))',
          700: 'color-mix(in oklch, var(--color-base-100) 15%, var(--color-base-content))',
          800: 'color-mix(in oklch, var(--color-base-100) 10%, var(--color-base-content))',
          900: 'color-mix(in oklch, var(--color-base-100) 5%,  var(--color-base-content))',
          950: 'var(--color-base-content)',
        },
        primary: {
          color:          '{primary.500}',
          contrastColor:  'var(--color-primary-content)',
          hoverColor:     '{primary.400}',
          activeColor:    '{primary.300}',
        },
        highlight: {
          background:       '{primary.950}',
          focusBackground:  '{primary.900}',
          color:            '{primary.200}',
          focusColor:       '{primary.100}',
        },
        text: {
          color:            'var(--color-base-content)',
          hoverColor:       'var(--color-base-content)',
          mutedColor:       'color-mix(in oklch, var(--color-base-content) 55%, var(--color-base-100))',
          hoverMutedColor:  'color-mix(in oklch, var(--color-base-content) 70%, var(--color-base-100))',
        },
        content: {
          background:       'var(--color-base-100)',
          hoverBackground:  'var(--color-base-200)',
          borderColor:      'var(--color-base-300)',
          color:            'var(--color-base-content)',
          hoverColor:       'var(--color-base-content)',
        },
        overlay: {
          select: {
            background:   'var(--color-base-200)',
            borderColor:  'var(--color-base-300)',
            color:        'var(--color-base-content)',
          },
          popover: {
            background:   'var(--color-base-200)',
            borderColor:  'var(--color-base-300)',
            color:        'var(--color-base-content)',
          },
          modal: {
            background:   'var(--color-base-200)',
            borderColor:  'var(--color-base-300)',
            color:        'var(--color-base-content)',
          },
        },
        formField: {
          background:         'var(--color-base-200)',
          disabledBackground: 'var(--color-base-300)',
          filledBackground:   'var(--color-base-300)',
          borderColor:        'var(--color-base-300)',
          hoverBorderColor:   'var(--color-primary)',
          focusBorderColor:   'var(--color-primary)',
          color:              'var(--color-base-content)',
          placeholderColor:   'color-mix(in oklch, var(--color-base-content) 50%, var(--color-base-200))',
        },
        list: {
          option: {
            focusBackground:          'var(--color-base-300)',
            selectedBackground:       '{primary.950}',
            selectedFocusBackground:  '{primary.900}',
            color:                    'var(--color-base-content)',
            focusColor:               'var(--color-base-content)',
            selectedColor:            '{primary.200}',
            selectedFocusColor:       '{primary.100}',
          },
        },
      },
    },
  },
});

export default DaisyPrimePreset;