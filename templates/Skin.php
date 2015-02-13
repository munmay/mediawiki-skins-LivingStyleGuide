<?php return function ($in, $debugopt = 1) {
    $cx = array(
        'flags' => array(
            'jstrue' => false,
            'jsobj' => false,
            'spvar' => true,
            'prop' => false,
            'method' => false,
            'mustlok' => true,
            'echo' => false,
            'debug' => $debugopt,
        ),
        'constants' =>  array(
            'DEBUG_ERROR_LOG' => 1,
            'DEBUG_ERROR_EXCEPTION' => 2,
            'DEBUG_TAGS' => 4,
            'DEBUG_TAGS_ANSI' => 12,
            'DEBUG_TAGS_HTML' => 20,
        ),
        'helpers' => array(            'msg' => function( array $args, array $named ) {
				$message = null;
				$str = array_shift( $args );

				return wfMessage( $str )->params( $args )->text();
			},
),
        'blockhelpers' => array(),
        'hbhelpers' => array(),
        'partials' => array(),
        'scopes' => array(),
        'sp_vars' => array('root' => $in),
'funcs' => array(
    'v' => function ($cx, $base, $path) {
        $count = count($cx['scopes']);
        while ($base) {
            $v = $base;
            foreach ($path as $name) {
                if (is_array($v) && isset($v[$name])) {
                    $v = $v[$name];
                    continue;
                }
                if (is_object($v)) {
                    if ($cx['flags']['prop'] && isset($v->$name)) {
                        $v = $v->$name;
                        continue;
                    }
                    if ($cx['flags']['method'] && is_callable(array($v, $name))) {
                        $v = $v->$name();
                        continue;
                    }
                }
                if ($cx['flags']['mustlok']) {
                    unset($v);
                    break;
                }
                return null;
            }
            if (isset($v)) {
                return $v;
            }
            $count--;
            switch ($count) {
                case -1:
                    $base = $cx['sp_vars']['root'];
                    break;
                case -2;
                    return null;
                default:
                    $base = $cx['scopes'][$count];
            }
        }
    },
    'sec' => function ($cx, $v, $in, $each, $cb, $else = null) {
        $isAry = is_array($v);
        $isTrav = $v instanceof Traversable;
        $loop = $each;
        $keys = null;
        $last = null;
        $isObj = false;

        if ($isAry && $else !== null && count($v) === 0) {
            $cx['scopes'][] = $in;
            $ret = $else($cx, $in);
            array_pop($cx['scopes']);
            return $ret;
        }

        // #var, detect input type is object or not
        if (!$loop && $isAry) {
            $keys = array_keys($v);
            $loop = (count(array_diff_key($v, array_keys($keys))) == 0);
            $isObj = !$loop;
        }

        if (($loop && $isAry) || $isTrav) {
            if ($each && !$isTrav) {
                // Detect input type is object or not when never done once
                if ($keys == null) {
                    $keys = array_keys($v);
                    $isObj = (count(array_diff_key($v, array_keys($keys))) > 0);
                }
            }
            $ret = array();
            $cx['scopes'][] = $in;
            $i = 0;
            if ($cx['flags']['spvar']) {
                $old_spvar = $cx['sp_vars'];
                $cx['sp_vars'] = array(
                    '_parent' => $old_spvar,
                    'root' => $old_spvar['root'],
                );
                if (!$isTrav) {
                    $last = count($keys) - 1;
                }
            }
            foreach ($v as $index => $raw) {
                if ($cx['flags']['spvar']) {
                    $cx['sp_vars']['first'] = ($i === 0);
                    $cx['sp_vars']['last'] = ($i == $last);
                    $cx['sp_vars']['key'] = $index;
                    $cx['sp_vars']['index'] = $i;
                    $i++;
                }
                $ret[] = $cb($cx, $raw);
            }
            if ($cx['flags']['spvar']) {
                if ($isObj) {
                    unset($cx['sp_vars']['key']);
                } else {
                    unset($cx['sp_vars']['last']);
                }
                unset($cx['sp_vars']['index']);
                unset($cx['sp_vars']['first']);
                $cx['sp_vars'] = $old_spvar;
            }
            array_pop($cx['scopes']);
            return join('', $ret);
        }
        if ($each) {
            if ($else !== null) {
                $cx['scopes'][] = $in;
                $ret = $else($cx, $v);
                array_pop($cx['scopes']);
                return $ret;
            }
            return '';
        }
        if ($isAry) {
            $cx['scopes'][] = $in;
            $ret = $cb($cx, $v);
            array_pop($cx['scopes']);
            return $ret;
        }

        if ($v === true) {
            return $cb($cx, $in);
        }

        if (!is_null($v) && ($v !== false)) {
            return $cb($cx, $v);
        }

        if ($else !== null) {
            $cx['scopes'][] = $in;
            $ret = $else($cx, $in);
            array_pop($cx['scopes']);
            return $ret;
        }

        return '';
    },
    'ifvar' => function ($cx, $v) {
        return !is_null($v) && ($v !== false) && ($v !== 0) && ($v !== 0.0) && ($v !== '') && (is_array($v) ? (count($v) > 0) : true);
    },
    'ch' => function ($cx, $ch, $vars, $op) {
        return $cx['funcs']['chret'](call_user_func_array($cx['helpers'][$ch], $vars), $op);
    },
    'chret' => function ($ret, $op) {
        if (is_array($ret)) {
            if (isset($ret[1]) && $ret[1]) {
                $op = $ret[1];
            }
            $ret = $ret[0];
        }

        switch ($op) {
            case 'enc':
                return htmlentities($ret, ENT_QUOTES, 'UTF-8');
            case 'encq':
                return preg_replace('/&#039;/', '&#x27;', htmlentities($ret, ENT_QUOTES, 'UTF-8'));
        }
        return $ret;
    },
)

    );
    
    return ''.$cx['funcs']['v']($cx, $in, array('headelement')).'

<div id="off-navigation">
'.'	<div class="dropdown">
		<button class="btn btn-default dropdown-toggle" type="button" id="account" data-toggle="dropdown" aria-expanded="true">
			<span class="glyphicon glyphicon-user"></span>
			<span class="caret"></span>
		</button>
		<ul class="dropdown-menu" role="menu" aria-labelledby="account">
'.$cx['funcs']['sec']($cx, $cx['funcs']['v']($cx, $in, array('personal_urls')), $in, true, function($cx, $in) {return '				<li><a href="'.htmlentities((string)$cx['funcs']['v']($cx, $in, array('href')), ENT_QUOTES, 'UTF-8').'" class="'.htmlentities((string)$cx['funcs']['v']($cx, $in, array('class')), ENT_QUOTES, 'UTF-8').'">'.htmlentities((string)$cx['funcs']['v']($cx, $in, array('text')), ENT_QUOTES, 'UTF-8').'</a></li>
';}).'		</ul>
	</div>
	<h2 id="navigation-header">Navigation</h2>
	<ul aria-labelledby="navigation-header">
'.$cx['funcs']['sec']($cx, $cx['funcs']['v']($cx, $in, array('sidebar','navigation')), $in, true, function($cx, $in) {return '			<li id="'.htmlentities((string)$cx['funcs']['v']($cx, $in, array('id')), ENT_QUOTES, 'UTF-8').'">
				<a href="'.htmlentities((string)$cx['funcs']['v']($cx, $in, array('href')), ENT_QUOTES, 'UTF-8').'">'.htmlentities((string)$cx['funcs']['v']($cx, $in, array('text')), ENT_QUOTES, 'UTF-8').'</a>
			</li>
';}).'	</ul>
'.'	</div>

	<div id="site-wrap">
		<nav class="navbar">
			<div class="navbar-header">
				<a class="navbar-brand" href="javascript:void(0)">'.htmlentities((string)$cx['funcs']['v']($cx, $in, array('sitename')), ENT_QUOTES, 'UTF-8').'</a>
			</div>

			<div class="collapse navbar-collapse" id="topmenu">
				<form class="navbar-form navbar-left" action="'.$cx['funcs']['v']($cx, $in, array('scriptpath')).'" role="search">
				<div class="form-group">
						<input
							type="text"
							id="searchInput"
							class="form-control search-input"
							placeholder="Search"
							name="search"
						>
						<input type="hidden" name="title" value="Special:Search">
					</div>
				</form>

				<ul class="nav navbar-nav navbar-right">
					<li><a href="#">Desktop</a></li>
					<li><a href="#">Mobile</a></li>
				</ul>
			</div>
		</nav>

		<div class="container" id="content">
			<h1 class="firstHeading" id="firstHeading">
				'.$cx['funcs']['v']($cx, $in, array('title')).'
				<span class="content-actions" id="p-cactions">
'.$cx['funcs']['sec']($cx, $cx['funcs']['v']($cx, $in, array('content_navigation','views')), $in, true, function($cx, $in) {return '						<a
							href="'.htmlentities((string)$cx['funcs']['v']($cx, $in, array('href')), ENT_QUOTES, 'UTF-8').'"
							class="'.htmlentities((string)$cx['funcs']['v']($cx, $in, array('class')), ENT_QUOTES, 'UTF-8').'"
							'.(($cx['funcs']['ifvar']($cx, $cx['funcs']['v']($cx, $in, array('rel')))) ? 'rel="'.htmlentities((string)$cx['funcs']['v']($cx, $in, array('rel')), ENT_QUOTES, 'UTF-8').'"' : '').'
							'.(($cx['funcs']['ifvar']($cx, $cx['funcs']['v']($cx, $in, array('id')))) ? 'id="'.htmlentities((string)$cx['funcs']['v']($cx, $in, array('id')), ENT_QUOTES, 'UTF-8').'"' : '').'
						>
							'.htmlentities((string)$cx['funcs']['v']($cx, $in, array('text')), ENT_QUOTES, 'UTF-8').'
						</a>
';}).'				</span>
			</h1>
'.(($cx['funcs']['ifvar']($cx, $cx['funcs']['v']($cx, $in, array('isarticle')))) ? '				<div id="siteSub">'.$cx['funcs']['ch']($cx, 'msg', array(array('tagline'),array()), 'enc').'</div>
' : '').'			<div id="contentSub" '.$cx['funcs']['v']($cx, $in, array('userlangattributes')).'>
				'.$cx['funcs']['v']($cx, $in, array('subtitle')).'
			</div>
'.(($cx['funcs']['ifvar']($cx, $cx['funcs']['v']($cx, $in, array('undelete')))) ? '				<div id="contentSub2">'.$cx['funcs']['v']($cx, $in, array('undelete')).'</div>
' : '').''.(($cx['funcs']['ifvar']($cx, $cx['funcs']['v']($cx, $in, array('newtalk')))) ? '				<div class="usermessage">'.htmlentities((string)$cx['funcs']['v']($cx, $in, array('newtalk')), ENT_QUOTES, 'UTF-8').'</div>
' : '').'			'.$cx['funcs']['v']($cx, $in, array('bodytext')).'
'.(($cx['funcs']['ifvar']($cx, $cx['funcs']['v']($cx, $in, array('printfooter')))) ? '				<div class="printfooter">
					'.$cx['funcs']['v']($cx, $in, array('printfooter')).'
				</div>
' : '').'		</div>
	</div>
</body>
'.$cx['funcs']['v']($cx, $in, array('debughtml')).'
'.$cx['funcs']['v']($cx, $in, array('bottomscripts')).'
'.$cx['funcs']['v']($cx, $in, array('reporttime')).'
</html>
';
}
?>