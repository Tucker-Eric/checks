<?php

namespace CheckWriter;

class Writer
{
    /**
     * @var FPDF
     */
    protected $pdf;

    /**
     * @var float
     */
    protected $page_width = 8.5;

    /**
     * @var int
     */
    protected $page_height = 11;

    /**
     * @var int
     */
    protected $left_margin = 0;

    /**
     * @var int
     */
    protected $top_margin = 0;

    /**
     * @var array
     */
    protected $cell_margin = ['top' => 0.1, 'right' => 0.25, 'bottom' => 0.25, 'left' => 0.25];

    // only used for making page breaks, no position calculations
    /**
     * @var int
     */
    protected $columns = 1;

    /**
     * @var float|int
     */
    protected $gutter = 3 / 16;

    /**
     * @var int
     */
    protected $rows = 1;

    /**
     * @var int
     */
    protected $logo_offset = 0;

    /**
     * @var float
     */
    protected $logo_width = 0.4;

    /**
     * @var
     */
    protected $check;

    /**
     * @var
     */
    protected $x;

    /**
     * @var
     */
    protected $y;

    public function __construct()
    {
        $this->pdf = new FPDF('P', 'in', [$this->page_width, $this->page_height]);
        $this->pdf->AddFont('Twcen');
        $this->pdf->AddFont('Micr');
        $this->pdf->AddFont('Courier');
        $this->pdf->SetMargins($this->left_margin, $this->top_margin);
        $this->pdf->SetDisplayMode("fullpage", "continuous");
        $this->pdf->AddPage();
    }

    /**
     * @param $pos
     * @return mixed
     */
    protected function cellMargin($pos)
    {
        return $this->cell_margin[$pos];
    }

    /**
     * @param Check $check
     */
    public function printCheck(Check $check)
    {
        $this->check = $check;
        $this->createBlankCheck()->fillCheck();
    }

    /**
     * @param int $offset
     * @return float
     */
    public function right($offset = 0)
    {
        return $this->page_width - $this->cellMargin('right') - .75 - $offset;
    }

    /**
     * @param int $offset
     * @return float
     */
    public function middle($offset = 0)
    {
        return ($this->page_width / 2) + $offset;
    }

    /**
     * @param int $offset
     * @return mixed
     */
    public function left($offset = 0)
    {
        return $this->x + $this->cellMargin('left') + $offset;
    }

    /**
     * @param int $offset
     * @return mixed
     */
    public function top($offset = 0)
    {
        return $this->y + $this->cellMargin('top') + $offset;
    }

    /**
     * @param $startX
     * @param $startY
     * @param $length
     * @param null $endY
     */
    public function line($startX, $startY, $length, $endY = null)
    {
        $this->pdf->Line($startX, $startY, ($startX + $length), $endY === null ? $startY : $endY);
    }

    /**
     * @return $this
     */
    public function printCheckNumber()
    {
        $this->pdf->SetFont('Twcen', '', 11);
        $this->pdf->SetXY($this->right(), $this->top());
        $this->pdf->Cell(1, (11 / 72), $this->check->check_number, 0, 'R');

        return $this;
    }

    /**
     * @return $this
     */
    public function addLogo()
    {
        if ($this->check->logo !== null) {
            $this->logo_offset = $this->logo_width + 0.005;  // width of logo
            $this->pdf->Image($this->check->logo, $this->left(), $this->top(.07), $this->logo_width);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function printFromName()
    {
        $this->pdf->SetFont('Twcen', '', 7);

        // name
        $this->pdf->SetXY($this->left($this->logo_offset), $this->top(0.1));

        $this->pdf->SetFont('Twcen', '', 10);
        $this->pdf->Cell(2, (10 / 72), $this->check->from_name, 0, 2);
        $this->pdf->SetFont('Twcen', '', 7);
        $this->pdf->Cell(2, (7 / 72), $this->check->from_address1, 0, 2);
        $this->pdf->Cell(2, (7 / 72), $this->check->from_address2, 0, 2);

        return $this;
    }

    /**
     * @return $this
     */
    public function printDate()
    {
        $this->line($this->middle(- 0.5), $this->top(.33), 1.65);
        $this->pdf->SetXY($this->middle(- .53), $this->top(.23));
        $this->pdf->Cell(1, (7 / 72), 'Date');

        return $this;
    }

    /**
     * @return $this
     */
    public function printPayee()
    {
        $this->line($this->left(), $this->top(.85), $this->right(1.35));
        $this->pdf->SetXY($this->left(), $this->top(.63));
        $this->pdf->MultiCell(.6, (7 / 72), 'Pay to the order of', 0, 'L');

        return $this;
    }

    /**
     * @return $this
     */
    public function printAmountBox()
    {
        // amount box
        $this->pdf->Rect($this->right(0.7), $this->top(0.58), 1.1, .25);

        // dollars
        $this->line($this->left(), $this->top(1.25), $this->right());
        $this->pdf->SetXY($this->right(0.5), $this->top(1.15));

        $this->pdf->Cell(1, (7 / 72), 'Dollars', '', '', 'R');

        return $this;
    }

    /**
     * @return $this
     */
    public function printBankInfo()
    {
        // bank info
        $this->pdf->SetXY($this->left(), $this->top(1.35));
        $this->pdf->Cell(2, (7 / 72), $this->check->bank_1, 0, 2);
        $this->pdf->Cell(2, (7 / 72), $this->check->bank_2, 0, 2);
        $this->pdf->Cell(2, (7 / 72), $this->check->bank_3, 0, 2);
        $this->pdf->Cell(2, (7 / 72), $this->check->bank_4, 0, 2);

        return $this;
    }

    /**
     * @return $this
     */
    public function printMemoLine()
    {
        $this->line($this->left(), $this->top(1.97), 3);
        $this->pdf->SetXY($this->left(), $this->top(1.875));
        $this->pdf->Cell(1, (7 / 72), 'Memo');

        return $this;
    }

    /**
     * @return $this
     */
    public function printSignatureLine()
    {
        $this->line($this->right(2.5), $this->top(1.97), 3.25);
        $this->pdf->SetXY($this->right(1.25), $this->top(2));
        $this->pdf->Cell(1, (7 / 72), 'Authorized Signature');

        return $this;
    }

    /**
     * @return $this
     */
    public function createBlankCheck()
    {
        return $this->printCheckNumber()
            ->addLogo()
            ->printFromName()
            ->printDate()
            ->printPayee()
            ->printAmountBox()
            ->printBankInfo()
            ->printMemoLine()
            ->printSignatureLine();
    }

    /**
     * @return $this
     */
    public function fillCheck()
    {
        return $this->writeDate()
            ->writePayee()
            ->writeAmount()
            ->writeMemo()
            ->writeAccountInfo()
            ->writeSignature()
            ->writeStubInfo();
    }

    /**
     * @return $this
     */
    public function writeDate()
    {
        if ($this->check->date !== null) {
            $this->pdf->SetFont('Courier', '', 11);
            $this->pdf->SetXY($this->middle(- 0.25), $this->top(0.13));
            $this->pdf->Cell(1, .25, $this->check->date);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function writePayee()
    {
        if ($this->check->pay_to !== null) {
            $this->pdf->SetFont('Courier', '', 11);
            $this->pdf->SetXY($this->left(0.5), $this->top(0.63));
            $this->pdf->Cell(1, .25, $this->check->pay_to);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function writeAmount()
    {
        if ($this->check->amount > 0) {
            $dollars = (int)$this->check->amount;
            $cents = round(($this->check->amount - $dollars) * 100);
            if ($cents === 0) {
                $cents = '00';
            }
            //$dollars_str = TextualNumber::GetText($dollars);
            $numtxt = new TextualNumber($dollars);
            $dollars_str = $numtxt->numToWords($dollars);

            $amt_string = '***'.ucfirst(strtolower($dollars_str))." dollars and $cents/100***";

            $this->pdf->SetFont('Courier', '', 9);
            $this->pdf->SetXY($this->left(), $this->top(1.03));
            $this->pdf->Cell(1, .25, $amt_string);

            $this->pdf->SetXY($this->right(0.6), $this->top(0.6));
            $this->pdf->Cell(1, .25, '$'.number_format($this->check->amount, 2));
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function writeMemo()
    {
        if ($this->check->memo !== null) {
            $this->pdf->SetFont('Courier', '', 8);
            $this->pdf->SetXY($this->left(0.36), $this->top(1.63));
            $this->pdf->drawTextBox($this->check->memo, 2.75, (1 / 3), 'L', 'B', false);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function writeAccountInfo()
    {
        $this->pdf->SetFont('Micr', '', 10);
        $this->pdf->SetXY($this->left(0.5), $this->top(2.22));
        $this->pdf->Cell(5, (10 / 72), $this->check->account_info);

        return $this;
    }

    /**
     * @return $this
     */
    public function writeSignature()
    {
        if ($this->check->signature !== null) {
            if (strtolower(substr($this->check->signature, - 3)) === 'png') {
                $sig_offset = 1.75;  // width of signature
                $this->pdf->Image($this->check->signature, $this->right(1.75), $this->top(1.25), $sig_offset);
            } else {
                $this->pdf->SetFont('Arial', 'i', 10);
                $this->pdf->SetXY($this->left(3.15), $this->top(1.76));
                $this->pdf->Cell(1, .25, $this->check->signature);

            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function writeStubInfo()
    {
        $this->pdf->SetFont('Courier', '', 9);
        // First partition
        foreach([3.5, 7] as $top) {
            $this->pdf->SetXY($this->left(), $this->top($top));
            $this->pdf->drawTextBox($this->check->check_stub, $this->middle(), 1, 'L', 'T', false);
            $this->pdf->SetXY($this->right(), $this->top($top));
            $this->pdf->Cell(1, .25, $this->check->check_number);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function writePreauthDisclaimer()
    {
        if ($this->check->pre_auth !== null) {
            $this->pdf->SetFont('Arial', '', 6);
            $this->pdf->SetXY($this->x + $this->cellMargin('left') + 3.3, $this->y + 2.155);
            $this->pdf->Cell(1, .25, "This check is pre-authorized by your depositor");
        }

        return $this;
    }

    /**
     * @param $checks
     * @return string
     */
    public function printChecks($checks)
    {

        ////////////////////////////
        // label-specific variables
        $label_height = 2.85;
        $label_width = 6;


        $lpos = 0;
        foreach ($checks as $check) {

            $pos = $lpos % ($this->rows * $this->columns);

            // calculate coordinates of top-left corner of current cell
            //    margin        cell offset
            $this->x = $this->cellMargin('left') + (($pos % $this->columns) * ($label_width + $this->gutter));
            //    margin        cell offset
            $this->y = $this->cellMargin('top') + (floor($pos / $this->columns) * $label_height);
            // Print the check
            $this->printCheck($check);
            // Create another page if needed
            if ($pos === (($this->rows * $this->columns) - 1) && ! ($lpos === count($checks) - 1)) {
                $this->pdf->AddPage();
            }

            $lpos ++;
        }

        return $this->pdf->Output();
    }
}
