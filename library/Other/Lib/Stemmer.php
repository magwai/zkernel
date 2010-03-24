<?php

class Zkernel_Other_Lib_Stemmer
{
    var $VERSION = "0.02";
    var $Stem_Caching = 0;
    var $Stem_Cache = array();
    var $VOWEL = '/���������/';
    var $PERFECTIVEGROUND = '/((��|����|������|��|����|������)|((?<=[��])(�|���|�����)))$/';
    var $REFLEXIVE = '/(�[��])$/';
    var $ADJECTIVE = '/(��|��|��|��|���|���|��|��|��|��|��|��|��|��|���|���|���|��|��|��|��|��|��)$/';
    var $PARTICIPLE = '/((���|���|���)|((?<=[��])(��|��|��|��|�)))$/';
    var $VERB = '/((���|���|���|����|����|���|���|���|��|��|��|��|��|��|���|���|���|���|��|�)|((?<=[��])(��|��|���|���|��|�|�|��|�|��|��|��|��|��|��|���|���)))$/';
    var $NOUN = '/(�|��|��|��|��|�|����|���|���|��|��|�|���|��|��|��|�|�|�|�|��|��|�|��|��|�)$/';
    var $RVRE = '/^(.*?[���������])(.*)$/';
    var $DERIVATIONAL = '/[^���������][���������]+[^���������]+[���������].*(?<=�)���?$/';

    function s(&$s, $re, $to)
    {
        $orig = $s;
        $s = preg_replace($re, $to, $s);
        return $orig !== $s;
    }

    function m($s, $re)
    {
        return preg_match($re, $s);
    }

    function stem_word($word)
    {
        $word = strtolower($word);
        $word = strtr($word, '�', '�');
        # Check against cache of stemmed words
        if ($this->Stem_Caching && isset($this->Stem_Cache[$word])) {
            return $this->Stem_Cache[$word];
        }
        $stem = $word;
        do {
          if (!preg_match($this->RVRE, $word, $p)) break;
          $start = $p[1];
          $RV = $p[2];
          if (!$RV) break;

          # Step 1
          if (!$this->s($RV, $this->PERFECTIVEGROUND, '')) {
              $this->s($RV, $this->REFLEXIVE, '');

              if ($this->s($RV, $this->ADJECTIVE, '')) {
                  $this->s($RV, $this->PARTICIPLE, '');
              } else {
                  if (!$this->s($RV, $this->VERB, ''))
                      $this->s($RV, $this->NOUN, '');
              }
          }

          # Step 2
          $this->s($RV, '/�$/', '');

          # Step 3
          if ($this->m($RV, $this->DERIVATIONAL))
              $this->s($RV, '/����?$/', '');

          # Step 4
          if (!$this->s($RV, '/�$/', '')) {
              $this->s($RV, '/����?/', '');
              $this->s($RV, '/��$/', '�');
          }

          $stem = $start.$RV;
        } while(false);
        if ($this->Stem_Caching) $this->Stem_Cache[$word] = $stem;
        return $stem;
    }

    function stem_caching($parm_ref)
    {
        $caching_level = @$parm_ref['-level'];
        if ($caching_level) {
            if (!$this->m($caching_level, '/^[012]$/')) {
                die(__CLASS__ . "::stem_caching() - Legal values are '0','1' or '2'. '$caching_level' is not a legal value");
            }
            $this->Stem_Caching = $caching_level;
        }
        return $this->Stem_Caching;
    }

    function clear_stem_cache()
    {
        $this->Stem_Cache = array();
    }
}