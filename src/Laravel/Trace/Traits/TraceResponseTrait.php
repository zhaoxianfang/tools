<?php

namespace zxf\Laravel\Trace\Traits;

use Illuminate\Http\Request;

/**
 * 把trace调试数据渲染到响应的html中
 */
trait TraceResponseTrait
{
    public function randerPage($trace)
    {
        $html = <<<EOT
    <div id="tools_trace">
    <div class="trace-logo">
      <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAAAXNSR0IArs4c6QAAAT9JREFUOE+tVFuOwjAMtMtqF06FtOVc/eBcFCmn2oegWU1Su7ZJ6X4QCakJycx4/OCc845euHgT8Ho4Fr7Pr+R4cR7PiGgdcNwNlPORCD8sTnSa+vJZ/psGdzazPQKCefoeFQQf3cdZVSpYIGkCWjDuztTfK5BdF775A07EnOSuVyiXu32v/oAk/9aw+T0t6gPRKb+VK5oUCcUqu3SjeojzNUDzZgEUdTOTGi9AyKjzT0zz1lRA8U6Y4t5mVggk4lA6bUBRIl5GO2AFMi9gZu8BI4DdQxGyHtUH8uDhXLytkCXENfUPWS4ZJdJukAxbz1A+6BBXCajLpYuel00rq60WbJYN1ImqZ4UtiVixxXfKf1rPDQdYVDtEVnva2FARjrbdz1AfYgKZ6bMJqCrs+FINydVgaOntARsebG1fDvgH5+lCJDZyUmAAAAAASUVORK5CYII=" alt="Logo" class="logo">
      <span class="title">Trace</span>
    </div>
    <div class="tabs-container">
      <div class="tabs-header">
        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAAAXNSR0IArs4c6QAAAT9JREFUOE+tVFuOwjAMtMtqF06FtOVc/eBcFCmn2oegWU1Su7ZJ6X4QCakJycx4/OCc845euHgT8Ho4Fr7Pr+R4cR7PiGgdcNwNlPORCD8sTnSa+vJZ/psGdzazPQKCefoeFQQf3cdZVSpYIGkCWjDuztTfK5BdF775A07EnOSuVyiXu32v/oAk/9aw+T0t6gPRKb+VK5oUCcUqu3SjeojzNUDzZgEUdTOTGi9AyKjzT0zz1lRA8U6Y4t5mVggk4lA6bUBRIl5GO2AFMi9gZu8BI4DdQxGyHtUH8uDhXLytkCXENfUPWS4ZJdJukAxbz1A+6BBXCajLpYuel00rq60WbJYN1ImqZ4UtiVixxXfKf1rPDQdYVDtEVnva2FARjrbdz1AfYgKZ6bMJqCrs+FINydVgaOntARsebG1fDvgH5+lCJDZyUmAAAAAASUVORK5CYII=" alt="Logo" class="tabs-logo-small">
        <div class="tabs-menu">
EOT;

        $tabNames = array_keys($trace);
        // tab name
        foreach ($tabNames as $key => $name) {
            $tabKey = ($key + 1);
            $html   .= "<div class='tabs-item " . ($key < 1 ? 'active' : '') . "' data-tab='tab" . $tabKey . "'>" . $name . "</div>";
        }

        $html .= <<<EOT
        </div>
        <div class="tabs-close">关闭</div>
      </div>
EOT;

        $tabIndex = 0;
        // tab content
        foreach ($trace as $key => $tabs) {
            $tabKey = ($tabIndex + 1);
            $tabIndex++;
            $active = ($tabIndex < 2 ? 'active' : '');
            $html   .= <<<EOT
        <div id="tab{$tabKey}" class="tabs-content {$active}">
<ul>
EOT;
            foreach ($tabs as $k => $item) {
                $html .= '<li>';
                if (is_numeric($k)) {
                    if (!empty($item['type']) && $item['type'] == 'trace') {
                        // trace 数据跟踪信息打印
                        $html .= $this->handleTraceData($item);
                    } else {
                        if (isset($item['label'])) {
                            $html .= "<span class='json-label'>{$item['label']}</span>";
                        }
                        if (is_array($item) && !empty($item)) {
                            $arrayString = json_encode($item, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                            $html        .= <<<EOT
                    <div class="json-arrow-pre-wrapper">
                      <span class="json-arrow" onclick="toggleJson(this)">▶</span>
                      <pre class="json">{$arrayString}</pre>
                    </div>
EOT;
                        } else {
                            $html .= "<span class='json-label'>" . (is_array($item) ? 'array[]' : $item) . "</span>";
                        }
                    }
                } else {
                    $html .= "<span class='json-label'>{$k}</span>";
                    if (is_array($item) && !empty($item)) {
                        $arrayString = empty($item) ? '[]' : json_encode($item, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                        $html        .= <<<EOT
                    <div class="json-arrow-pre-wrapper">
                      <span class="json-arrow" onclick="toggleJson(this)">▶</span>
                      <pre class="json">{$arrayString}</pre>
                    </div>
EOT;
                    } else {
                        $html .= "<div class='json-string-content'>" . (is_array($item) ? '[]' : $item) . "</div>";
                    }
                }
                if (!empty($item['right'])) {
                    $html .= "<span class='json-right'>" . $item['right'] . "</span>";
                } else {
                    $html .= "<span class='json-right'></span>";
                }
                $html .= '</li>';
            }

            $html .= <<<EOT
        </ul>
       </div>
EOT;
        }

        $html .= <<<EOT
      </div></div>
EOT;

        return $html;
    }

    protected function handleTraceData($data = []): string
    {

        $str = '<span class="json-label"><a href="phpstorm://open?file=' . urlencode($data['file_path']) . '&amp;line=' . $data['line'] . '" class="phpdebugbar-link">' . $data['local'] . '</a></span>';

        if (is_array($data['var']) && !empty($data['var'])) {
            $arrayString = json_encode($data['var'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $str         .= <<<EOT
                    <div class="json-arrow-pre-wrapper">
                      <span class="json-arrow" onclick="toggleJson(this)">▶</span>
                      <pre class="json">{$arrayString}</pre>
                    </div>
EOT;
        } else {
            $str .= "<div class='json-string-content'>" . (is_array($data['var']) ? '[]' : $data['var']) . "</div>";
        }
        return $str;
    }

    /**
     * 把trace数据渲染到响应的html中
     *
     * @param Request $request
     * @param         $response
     *
     * @return mixed
     */
    public function renderTraceStyleAndScript(Request $request, $response): mixed
    {
        if (!is_enable_trace()) {
            return $response;
        }

        $traceContent = $this->output($response);
        if (empty($traceContent)) {
            return $response;
        }

        $content = $response->getContent();
        if (!$request->isMethod('get')) {
            try {
                $content = json_decode($content, true);
            } catch (\Exception $e) {
            }
            $content['_debugger'] = $traceContent;
            $content              = json_encode($content, JSON_UNESCAPED_UNICODE);
            $response->setContent($content);
            $response->headers->remove('Content-Length');
            return $response;
        }

        $cssRoute = preg_replace('/\Ahttps?:/', '', route('debugger.trace.css'));
        $jsRoute  = preg_replace('/\Ahttps?:/', '', route('debugger.trace.js'));

        $style  = "<link rel='stylesheet' type='text/css' property='stylesheet' href='{$cssRoute}'  data-turbolinks-eval='false' data-turbo-eval='false'>";
        $script = "<script src='{$jsRoute}' type='text/javascript'  data-turbolinks-eval='false' data-turbo-eval='false' ></script>";

        $posCss = strripos($content, '</head>');
        if (false !== $posCss) {
            $content = substr($content, 0, $posCss) . PHP_EOL . $style . PHP_EOL . substr($content, $posCss);
        } else {
            $content = $style . PHP_EOL . $content;
        }

        $posJs = strripos($content, '</body>');
        if (false !== $posJs) {
            $content = substr($content, 0, $posJs) . PHP_EOL . $traceContent . PHP_EOL . $script . substr($content, $posJs);
            // set_protected_value($response, 'content', $traceContent);
        } else {
            $content = $content . PHP_EOL . $traceContent . PHP_EOL . $script;
        }

        $response->setContent($content);
        $response->headers->remove('Content-Length');

        if ($original = $response->getOriginalContent()) {
            $response->original = $original;
        }

        return $response;
    }
}