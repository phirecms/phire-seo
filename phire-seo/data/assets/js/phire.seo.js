/**
 * SEO Module Scripts for Phire CMS 2
 */

jax(document).ready(function(){
    if (jax('#content-form')[0] != undefined) {
        var phireCookie = jax.cookie.load('phire');
        var path        = phireCookie.base_path + phireCookie.app_uri;
        var seoFields   = jax.get(path + '/seo/json');
        var limit       = 0;

        for (var name in seoFields) {
            if (seoFields[name] != '') {
                switch (name) {
                    case 'seo_title':
                        limit = 60;
                        break;
                    case 'description':
                        limit = 160;
                        break;
                    case 'keywords':
                        limit = 255;
                        break;

                }
                var field = 'field_' + seoFields[name];
                if (jax('#' + field)[0] != undefined) {
                    jax('#' + field).data('limit', limit);
                    jax('#' + field).data('field', field);
                    jax('#' + field).data('label', jax('label[for=' + field + ']').val());
                    jax('#' + field).keyup(function(){
                        if (this.value.length > jax(this).data('limit')) {
                            jax('label[for=' + jax(this).data('field') + ']').val(jax(this).data('label') +
                                ' <strong class="red float-right">Limit of ' + jax(this).data('limit') + ' characters exceeded.</strong>');
                        } else {
                            jax('label[for=' + jax(this).data('field') + ']').val(jax(this).data('label'));
                        }
                    });

                    if (jax('#' + field).val().length > limit) {
                        jax('label[for=' + field + ']').val(jax('label[for=' + field + ']').val() +
                            ' <strong class="red float-right">Limit of ' + limit + ' characters exceeded.</strong>');
                    }
                }
            }
        }
    }
});