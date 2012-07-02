<?php



/**
 * Skeleton subclass for performing query and update operations on the 'chart' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.datawrapper
 */
class ChartQuery extends BaseChartQuery {

    /**
     * creates a new empty chart
     */
    public function createEmptyChart($user) {
        $i = 0;
        while ($i++ < 10) {
            try {
                $chart = new Chart();
                $chart->setId(self::_rand_chars(5));
                print $chart->getId();
                $chart->setCreatedAt(time());
                $chart->setLastModifiedAt(time());
                if ($user->isLoggedIn()) {
                    $chart->setAuthorId($user->getId());
                } else {
                    // remember session id to be able to assign this chart
                    // to a newly registered user
                    $chart->setGuestSession(session_id());
                }
                // find a nice, more or less unique title
                $title = '[Untitled';
                $untitledCharts = $this->filterByAuthorId($user->getId())
                    ->filterByTitle('[Untitled%')
                    ->filterByDeleted(false)
                    ->find();
                if (count($untitledCharts) > 0) $title .= '-'.count($untitledCharts);
                $chart->setTitle($title . ']');

                // todo: use global default theme
                $chart->setTheme('default');
                $chart->setType('line-chart');

                $defaultMeta = array(
                    'data' => array(
                        'transpose' => true,
                        'horizontal-header' => true
                    ),
                    'visualize' => array(
                        'highlighted-series' => array(),
                        'highlighted-values' => array()
                    ),
                    'describe' => array(
                        'source-name' => '',
                        'source-url' => ''
                    )
                );

                $chart->setMetadata(json_encode($defaultMeta));
                // $chart->setLanguage($user->getLanguage());  // defaults to user language
                $chart->save();
                break;
            } catch (Exception $e) {
                print $e;
                continue;
            }
        }
        if ($chart->isNew()) {
            throw Exception('could not get an id for the chart');
        } else {
            return $chart;
        }
    }


    /*
     * generate a random id string for charts
     */
    protected static function _rand_chars($l, $u = FALSE) {
        // implementation taken from http://www.php.net/manual/de/function.rand.php#87487
        $c = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        if (!$u) for ($s = '', $i = 0, $z = strlen($c)-1; $i < $l; $x = rand(0,$z), $s .= $c{$x}, $i++);
        else for ($i = 0, $z = strlen($c)-1, $s = $c{rand(0,$z)}, $i = 1; $i != $l; $x = rand(0,$z), $s .= $c{$x}, $s = ($s{$i} == $s{$i-1} ? substr($s,0,-1) : $s), $i=strlen($s));
        return $s;
    }


    public function getPublicChartsByUser($user) {
        return $this->filterByAuthorId($user->getId())
            ->filterByDeleted(false)
            ->orderByLastModifiedAt('desc')
            ->find();
    }


} // ChartQuery