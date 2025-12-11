#!/usr/bin/env powershell

# ============================================
# Script de Análise de Cobertura de Testes - PNSL-NTM
# VERSÃO FINAL E FUNCIONAL
# ============================================

# Define Cores para o Output
$RED = "Red"
$GREEN = "Green"
$YELLOW = "Yellow"
$BLUE = "Blue"
$MAGENTA = "Magenta"
$GRAY = "Gray"
$WHITE = "White"
$NC = $null 

# Função para calcular cobertura de um arquivo (com tipagem forçada)
function Get-FileCoverage {
    param($file)

    # Adiciona null-check para metrics, caso o file exista mas a métrica não
    if (-not $file.metrics) { return 0 }

    $metrics = $file.metrics
    [int]$statements = $metrics.statements
    [int]$coveredstatements = $metrics.coveredstatements

    if ($statements -eq 0) {
        return 100
    }
    return [math]::Round(($coveredstatements / $statements) * 100, 2)
}

# Função para obter nome curto do arquivo (Compatível com Windows e Linux/WSL)
function Get-ShortName {
    param($fullPath)

    $parts = $fullPath -split '[\\/]'
    return $parts[-1] -replace "\.php$", ""
}

# --- Cabeçalho ---
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " ${WHITE} Analise de Cobertura de Testes" -ForegroundColor Cyan
Write-Host " ${WHITE} PNSL-NTM" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# --- 1. Verificação do Arquivo e Leitura do XML ---
if (-not (Test-Path "coverage.xml")) {
    Write-Host "Arquivo coverage.xml nao encontrado!" -ForegroundColor Red
    Write-Host "Execute primeiro: sail test --coverage --min=0" -ForegroundColor Yellow
    exit 1
}

# ----------------------------------------------------------------------------------
# MÉTODO ANTIGO DE LEITURA XML (Compátivel com Powershell 5.1, sem ConvertFrom-Xml)
# ----------------------------------------------------------------------------------
try {
    # Lê o arquivo diretamente como string e converte para XML
    [xml]$coverage = [System.IO.File]::ReadAllText("coverage.xml", [System.Text.Encoding]::UTF8)
}
catch {
    Write-Host "Erro ao ler o arquivo XML. Verifique o formato do XML: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}


# Coletar dados de cobertura
$coverageData = @()

# Garante que $packages é um array válido
$packages = @($coverage.coverage.project.package)

foreach ($package in $packages) {
  
    # 1. Checa se o elemento 'package' tem o método SelectNodes.
    if (-not $package.SelectNodes) { continue }
  
    # 2. Obtém a lista bruta de nós de arquivo (pode incluir TextNodes).
    # MUDANÇA CRÍTICA: Uso de './/file' para busca recursiva (ignora tags <directory>).
    $filesBruto = $package.SelectNodes(".//file")
  
    # --- FILTRAGEM MAIS AGRESSIVA (BYPASS DA CONVERSÃO IMPLÍCITA) ---
    # Filtra os nós brutos para obter SOMENTE os elementos XML (não os TextNodes).
    $files = @($filesBruto) | Where-Object { 
        $_.GetType().Name -eq 'XmlElement' 
    }
    # -----------------------------------------------------------------
  
    # Se a coleção filtrada for vazia ou nula, pulamos.
    if (-not $files) { continue } 

    # --- INÍCIO DO LAÇO FINAL ---
    foreach ($file in $files) {
    
        # CHECAGEM: Se o nó do arquivo não tiver as propriedades de métricas necessárias, pule.
        if (-not $file.name -or -not $file.metrics) { continue } 

        $fullPath = $file.name
        $shortName = Get-ShortName $fullPath

        $fileCoverage = Get-FileCoverage $file

        $metrics = $file.metrics

        $coverageData += [PSCustomObject]@{
            Name              = $shortName
            Category          = $category
            Coverage          = $fileCoverage
            Statements        = [int]$metrics.statements
            CoveredStatements = [int]$metrics.coveredstatements
            Methods           = [int]$metrics.methods
            CoveredMethods    = [int]$metrics.coveredmethods
            FullPath          = $fullPath
        }
    }
}

# ============================================
# RELATÓRIOS E PLANO DE AÇÃO
# ============================================

# Calcular estatísticas gerais
$totalStatements = ($coverageData | Measure-Object -Property Statements -Sum).Sum
$totalCovered = ($coverageData | Measure-Object -Property CoveredStatements -Sum).Sum
$overallCoverage = if ($totalStatements -gt 0) { [math]::Round(($totalCovered / $totalStatements) * 100, 2) } else { 0 }

Write-Host "RESUMO GERAL" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Green
$coverageColor = if ($overallCoverage -ge 80) { "Green" } elseif ($overallCoverage -ge 60) { "Yellow" } else { "Red" }
Write-Host "Cobertura Total: $overallCoverage%" -ForegroundColor $coverageColor
Write-Host "Total de Statements: $totalStatements"
Write-Host "Statements Cobertos: $totalCovered"
Write-Host ""

# Estatísticas por categoria
Write-Host "COBERTURA POR CATEGORIA" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Gray

$categories = $coverageData | Group-Object Category | Sort-Object Name
foreach ($cat in $categories) {
    $catStatements = ($cat.Group | Measure-Object -Property Statements -Sum).Sum
    $catCovered = ($cat.Group | Measure-Object -Property CoveredStatements -Sum).Sum
    $catCoverage = if ($catStatements -gt 0) { [math]::Round(($catCovered / $catStatements) * 100, 2) } else { 0 }

    $color = if ($catCoverage -ge 80) { "Green" } elseif ($catCoverage -ge 60) { "Yellow" } else { "Red" }
    Write-Host "$($cat.Name): " -NoNewline -ForegroundColor $WHITE
    Write-Host "$catCoverage%" -ForegroundColor $color -NoNewline
    Write-Host " ($catCovered/$catStatements statements)"
}

Write-Host ""

# Arquivos com cobertura proxima a 80 porcento (70-85)
Write-Host "ARQUIVOS PROXIMOS A 80 PORCENTO (70-85)" -ForegroundColor Magenta
Write-Host "======================================" -ForegroundColor Gray
$nearTarget = $coverageData | Where-Object { [decimal]$_.Coverage -ge 70 -and [decimal]$_.Coverage -lt 85 } | Sort-Object Coverage -Descending
if ($nearTarget.Count -gt 0) {
    foreach ($item in $nearTarget) {
        $missing = $item.Statements - $item.CoveredStatements
        Write-Host " - $($item.Name) ($($item.Category)): " -NoNewline -ForegroundColor $WHITE
        Write-Host "$($item.Coverage)%" -ForegroundColor Yellow -NoNewline
        Write-Host " - faltam $missing statements"
    }
}
else {
    Write-Host " Nenhum arquivo nesta faixa" -ForegroundColor Gray
}

Write-Host ""

# Arquivos com baixa cobertura (< 50%) e com mais de 10 statements
Write-Host "ARQUIVOS COM BAIXA COBERTURA (menor que 50 porcento)" -ForegroundColor Red
Write-Host "======================================" -ForegroundColor Gray
$lowCoverage = $coverageData | Where-Object { [decimal]$_.Coverage -lt 50 -and [int]$_.Statements -gt 10 } | Sort-Object Statements -Descending | Select-Object -First 10
if ($lowCoverage.Count -gt 0) {
    foreach ($item in $lowCoverage) {
        $missing = $item.Statements - $item.CoveredStatements
        Write-Host " - $($item.Name) ($($item.Category)): " -NoNewline -ForegroundColor $WHITE
        Write-Host "$($item.Coverage)%" -ForegroundColor Red -NoNewline
        Write-Host " - faltam $missing statements"
    }
}
else {
    Write-Host " Nenhum arquivo nesta faixa" -ForegroundColor Gray
}

Write-Host ""

# Arquivos sem cobertura (0%)
Write-Host "ARQUIVOS SEM COBERTURA (0 porcento)" -ForegroundColor Red
Write-Host "======================================" -ForegroundColor Gray
$noCoverage = $coverageData | Where-Object { $_.Coverage -eq 0 -and $_.Statements -gt 0 } | Sort-Object Statements -Descending
if ($noCoverage.Count -gt 0) {
    foreach ($item in $noCoverage) {
        Write-Host " - $($item.Name) ($($item.Category)): " -NoNewline -ForegroundColor $WHITE
        Write-Host "$($item.Statements) statements nao cobertos" -ForegroundColor Red
    }
}
else {
    Write-Host " Nenhum arquivo sem cobertura!" -ForegroundColor Green
}

Write-Host ""

# Plano de ação
Write-Host "PLANO DE ACAO RECOMENDADO" -ForegroundColor Magenta
Write-Host "======================================" -ForegroundColor Gray

$gap = 80 - $overallCoverage
if ($gap -gt 0) {
    Write-Host ""
    Write-Host "Meta: Aumentar cobertura de $overallCoverage% para 80%" -ForegroundColor Yellow
    Write-Host "Gap: $($gap.ToString('0.00'))%" -ForegroundColor Yellow
    Write-Host ""

    Write-Host "PRIORIDADE 1: Completar arquivos proximos a 80%" -ForegroundColor Cyan
    $priority1 = $coverageData | Where-Object { $_.Coverage -ge 70 -and $_.Coverage -lt 80 } | Sort-Object Coverage -Descending | Select-Object -First 5
    if ($priority1.Count -gt 0) {
        foreach ($item in $priority1) {
            $missing = $item.Statements - $item.CoveredStatements
            Write-Host " - $($item.Name): adicionar ~$missing testes" -ForegroundColor $WHITE
        }
    }
    else {
        Write-Host " Nenhum arquivo nesta categoria" -ForegroundColor Gray
    }

    Write-Host ""
    Write-Host "PRIORIDADE 2: Melhorar arquivos com cobertura media (50-70 porcento)" -ForegroundColor Cyan
    $priority2 = $coverageData | Where-Object { $_.Coverage -ge 50 -and $_.Coverage -lt 70 } | Sort-Object @{Expression = { $_.Statements - $_.CoveredStatements }; Descending = $true } | Select-Object -First 5
    if ($priority2.Count -gt 0) {
        foreach ($item in $priority2) {
            $missing = $item.Statements - $item.CoveredStatements
            Write-Host " - $($item.Name): adicionar ~$missing testes" -ForegroundColor $WHITE
        }
    }
    else {
        Write-Host " Nenhum arquivo nesta categoria" -ForegroundColor Gray
    }

    Write-Host ""
    Write-Host "PRIORIDADE 3: Criar testes para arquivos sem cobertura" -ForegroundColor Cyan
    $priority3 = $coverageData | Where-Object { $_.Coverage -eq 0 } | Sort-Object Statements -Descending | Select-Object -First 5
    if ($priority3.Count -gt 0) {
        foreach ($item in $priority3) {
            Write-Host " - $($item.Name): criar suite de testes completa" -ForegroundColor $WHITE
        }
    }
    else {
        Write-Host " Nenhum arquivo sem cobertura!" -ForegroundColor Green
    }
}
else {
    Write-Host "META ATINGIDA! Cobertura esta em $overallCoverage%" -ForegroundColor Green
    Write-Host ""
    Write-Host "Proximos passos:" -ForegroundColor Cyan
    Write-Host " - Manter cobertura acima de 80%" -ForegroundColor $WHITE
    Write-Host " - Adicionar testes para novas features" -ForegroundColor $WHITE
    Write-Host " - Revisar e melhorar testes existentes" -ForegroundColor $WHITE
}

Write-Host ""
Write-Host "======================================" -ForegroundColor Green
Write-Host ""

# Salvar relatório em arquivo
$reportPath = "coverage-report-$(Get-Date -Format 'yyyy-MM-dd-HHmmss').txt"
$coverageData | Sort-Object Coverage | Format-Table Name, Category, Coverage, Statements, CoveredStatements -AutoSize | Out-File $reportPath
Write-Host "Relatorio detalhado salvo em: $reportPath" -ForegroundColor Gray
Write-Host ""