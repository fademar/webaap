<?php
/**
 * Fonctions d'aide pour l'affichage des champs ACF
 *
 * @package msh-webaap
 */

if ( ! function_exists( 'mshps_render_acf_field_value' ) ) {
    /**
     * Rend la valeur HTML d'un champ ACF en fonction de son type.
     * Gère les cas complexes : Repeater, Group, Date, URL, Fichier, Image.
     *
     * @param array $field  Le tableau d'objet champ ACF.
     * @param mixed $value  (Optionnel) La valeur à afficher. Si null, utilise $field['value'].
     * @return string       Le HTML généré.
     */
    function mshps_render_acf_field_value( array $field, mixed $value = null ): string {
        $type  = $field['type'] ?? 'text';
        $val   = $value !== null ? $value : ( $field['value'] ?? null );

        $empty = $val === null || $val === '' || $val === [] || $val === false;
        if ( $empty ) {
            return '<span class="text-gray-400">—</span>';
        }

        // Date ACF souvent stockée en Ymd (ex: 20251212) → dd/mm/YYYY
        if ( is_string( $val ) && preg_match( '/^\d{8}$/', $val ) ) {
            $dt = DateTime::createFromFormat( 'Ymd', $val );
            if ( $dt ) {
                return esc_html( $dt->format( 'd/m/Y' ) );
            }
        }

        switch ( $type ) {
            case 'true_false':
                return $val ? 'Oui' : 'Non';

            case 'date_picker':
                if ( is_string( $val ) ) {
                    $dt = DateTime::createFromFormat( 'Ymd', $val );
                    if ( $dt ) {
                        return esc_html( $dt->format( 'd/m/Y' ) );
                    }
                }
                return esc_html( (string) $val );

            case 'textarea':
                $txt = (string) $val;
                $plain = trim( wp_strip_all_tags( $txt ) );
                if ( mb_strlen( $plain ) > 450 ) {
                    $preview = mb_substr( $plain, 0, 220 ) . '…';
                    return '<details class="group rounded-lg border border-gray-200 bg-gray-50 p-3">' .
                        '<summary class="cursor-pointer text-sm font-medium text-gray-700">Afficher le contenu</summary>' .
                        '<div class="mt-2 text-sm text-gray-700 whitespace-pre-line">' . esc_html( $txt ) . '</div>' .
                        '<div class="text-xs text-gray-500 mt-2">Aperçu : ' . esc_html( $preview ) . '</div>' .
                    '</details>';
                }
                return '<div class="whitespace-pre-line">' . esc_html( $txt ) . '</div>';

            case 'wysiwyg':
                $html = (string) $val;
                $plain = trim( wp_strip_all_tags( $html ) );
                if ( mb_strlen( $plain ) > 600 ) {
                    return '<details class="group rounded-lg border border-gray-200 bg-gray-50 p-3">' .
                        '<summary class="cursor-pointer text-sm font-medium text-gray-700">Afficher le contenu</summary>' .
                        '<div class="mt-2 prose prose-sm max-w-none">' . wp_kses_post( $html ) . '</div>' .
                    '</details>';
                }
                return '<div class="prose prose-sm max-w-none">' . wp_kses_post( $html ) . '</div>';

            case 'email':
                $email = (string) $val;
                return '<a class="text-blue-700 hover:underline" href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a>';

            case 'url':
                $url = (string) $val;
                return '<a class="text-blue-700 hover:underline" href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $url ) . '</a>';

            case 'select':
            case 'checkbox':
            case 'radio':
                if ( is_array( $val ) ) {
                    $items = array_map( static fn( $v ) => (string) $v, $val );
                    $items = array_filter( $items, static fn( $v ) => $v !== '' );
                    return esc_html( implode( ', ', $items ) );
                }
                return esc_html( (string) $val );

            case 'taxonomy':
                // Valeur possible: IDs, WP_Term, array de terms
                $names = [];
                if ( is_array( $val ) ) {
                    foreach ( $val as $t ) {
                        if ( is_object( $t ) && isset( $t->name ) ) {
                            $names[] = (string) $t->name;
                        } elseif ( is_numeric( $t ) ) {
                            $term = get_term( (int) $t );
                            if ( $term && ! is_wp_error( $term ) ) {
                                $names[] = (string) $term->name;
                            }
                        } else {
                            $names[] = (string) $t;
                        }
                    }
                } elseif ( is_object( $val ) && isset( $val->name ) ) {
                    $names[] = (string) $val->name;
                } elseif ( is_numeric( $val ) ) {
                    $term = get_term( (int) $val );
                    if ( $term && ! is_wp_error( $term ) ) {
                        $names[] = (string) $term->name;
                    }
                }
                $names = array_filter( $names, static fn( $n ) => $n !== '' );
                return $names ? esc_html( implode( ', ', $names ) ) : '<span class="text-gray-400">—</span>';

            case 'file':
                // Peut être ID ou array
                $file = is_array( $val ) ? $val : ( is_numeric( $val ) ? wp_get_attachment_metadata( (int) $val ) : null );
                $url  = '';
                $name = '';
                if ( is_array( $val ) ) {
                    $url  = (string) ( $val['url'] ?? '' );
                    $name = (string) ( $val['filename'] ?? $val['title'] ?? '' );
                } elseif ( is_numeric( $val ) ) {
                    $url  = (string) wp_get_attachment_url( (int) $val );
                    $name = (string) get_the_title( (int) $val );
                }
                if ( $url ) {
                    return '<a class="text-blue-700 hover:underline" href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $name ?: basename( $url ) ) . '</a>';
                }
                return '<span class="text-gray-400">—</span>';

            case 'image':
                if ( is_array( $val ) && ! empty( $val['url'] ) ) {
                    $url = (string) $val['url'];
                    return '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer"><img class="h-16 w-auto rounded-lg border border-gray-200" src="' . esc_url( $url ) . '" alt="" /></a>';
                }
                if ( is_numeric( $val ) ) {
                    $url = (string) wp_get_attachment_image_url( (int) $val, 'thumbnail' );
                    if ( $url ) {
                        return '<a href="' . esc_url( wp_get_attachment_url( (int) $val ) ) . '" target="_blank" rel="noopener noreferrer"><img class="h-16 w-auto rounded-lg border border-gray-200" src="' . esc_url( $url ) . '" alt="" /></a>';
                    }
                }
                return '<span class="text-gray-400">—</span>';

            case 'repeater':
                if ( ! is_array( $val ) || empty( $val ) ) {
                    return '<span class="text-gray-400">—</span>';
                }
                $sub_fields = $field['sub_fields'] ?? [];
                $rows = $val;

                // Repeater standard
                $html = '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg overflow-hidden">';
                $html .= '<thead class="bg-gray-50"><tr>';
                foreach ( $sub_fields as $sf ) {
                    $html .= '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">' . esc_html( $sf['label'] ?? $sf['name'] ?? '' ) . '</th>';
                }
                $html .= '</tr></thead><tbody class="divide-y divide-gray-200 bg-white">';
                foreach ( $rows as $row ) {
                    $html .= '<tr>';
                    foreach ( $sub_fields as $sf ) {
                        $name = $sf['name'] ?? '';
                        $cell_val = is_array( $row ) && $name !== '' ? ( $row[ $name ] ?? null ) : null;
                        $html .= '<td class="px-3 py-2 text-sm text-gray-700">' . mshps_render_acf_field_value( $sf, $cell_val ) . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</tbody></table></div>';
                return $html;

            case 'group':
                if ( ! is_array( $val ) || empty( $val ) ) {
                    return '<span class="text-gray-400">—</span>';
                }
                $sub_fields = $field['sub_fields'] ?? [];
                $html = '<div class="space-y-2">';
                foreach ( $sub_fields as $sf ) {
                    $name = $sf['name'] ?? '';
                    $sub_val = $name !== '' ? ( $val[ $name ] ?? null ) : null;
                    $html .= '<div class="grid grid-cols-1 md:grid-cols-3 gap-2">';
                    $html .= '<div class="text-xs font-medium text-gray-500">' . esc_html( $sf['label'] ?? $name ) . '</div>';
                    $html .= '<div class="md:col-span-2 text-sm text-gray-700">' . mshps_render_acf_field_value( $sf, $sub_val ) . '</div>';
                    $html .= '</div>';
                }
                $html .= '</div>';
                return $html;

            default:
                if ( is_array( $val ) ) {
                    return esc_html( wp_json_encode( $val ) );
                }
                return esc_html( (string) $val );
        }
    }
}

