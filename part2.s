la sp, stack_base
j main

; ==============================================================================
; === DATA SECTION

cLF		equ	10

start_msg defb "Type a message: \n\0"
in_msg defb "Encrypting: \0"
out_msg defb "Encrypted message: \0"
key_msg_real defb "Used Key: \0"
orig_msg defb "Original: \0"
end_msg defb "Closing...\0"

align

k defw 4

stack defs 128
stack_base
msg defs 256,0

; === END OF DATA SECTION
; ==============================================================================

align

; def is_letter(character):
;   if character < 'A':
;       return 0
;   if character > 'z':
;       return 0
;   if character <= 'Z':
;       return 1
;   if character < 'a':
;       return 0
;   return 1

is_letter
    ; Your code goes here
    ;jr ra
    
    li t0, 65
    blt a0, t0, check_lower
    

    li t0, 91
    blt a0, t0, letter

check_lower    
    li t0, 97
    blt a0, t0, not_letter
    
    li t0, 123
    blt a0, t0, letter
    
letter
   li a0, 1 
   jr ra
    
not_letter
   li a0, 0 
   jr ra
    
; ==============================================================

; def shift_char(character, k):
;   if is_letter(character):
;       get character offset
;       character = character + k
;       if character goes beyond alphabet:
;           wrap around to the beginning of alphabet
;   return character

shift_char
    ; Your code goes here
    ;jr ra
    
    addi sp, sp, -12
    sw ra, 8[sp]
    sw s0, 4[sp]
    sw s1, 0[sp]
    
    mv s0, a0
    mv s1, a1
    
    jal is_letter
    beqz a0, shift_non_letter
    
    li t0, 90
    ble s0, t0, shift_upper
    
    li t1, 97
    j shift_calculate
    
shift_upper
    li t1, 65
    
shift_calculate
    sub t2, s0, t1
    add t2, t2, s1
    
    li t3, 26
    blt t2, t3, shift_no_wrap
    addi t2, t2, -26
    
    
shift_no_wrap
    add a0, t2, t1
    j shift_done
    
    
shift_non_letter
    mv a0, s0
    
    
shift_done
    lw s1, 0[sp]
    lw s0, 4[sp]
    lw ra, 8[sp]
    addi sp, sp, 12
    jr ra


; ==============================================================

;TASK C
; def encrypt(message, k):
;   i = 0
;   while message[i] != 0:
;       c = shift_char(message[i], k)
;       message[i] = c
;       i = i + 1
;   return

encrypt
    addi sp, sp, -12
    sw ra, 8[sp]
    sw s0, 4[sp]
    sw s1, 0[sp]
    
    mv s0, a0
    mv s1, a1

encrypt_loop
    lbu a0, 0[s0]
    beqz a0, encrypt_done
    
    mv a1, s1
    jal shift_char
    
    sb a0, 0[s0]
    
    addi s0, s0, 1
    j encrypt_loop
    
encrypt_done
    lw s1, 0[sp]
    lw s0, 4[sp]
    lw ra, 8[sp]
    
    addi sp, sp, 12
    
    jr ra
; ==============================================================

;TASK D
; def decrypt(message, k):
;   encrypt(...)
;   return

decrypt
    li t0, 26
    sub a1, t0, a1
    j encrypt

; ==============================================================

; def main

main
    li a7, 2
    la a0, start_msg
    ecall
    la a1 , msg
    li a2, cLF
    addi a3, a1, 255
loop_read
    li a7, 1	
    ecall
    beq a0, a2, end_read
    sb a0, 0[a1]
    li a7, 0
    ecall
    addi a1, a1, 1
    bge a1, a3, end_read
    j loop_read
end_read
    xor a0, a0, a0
    sb a0, 0[a1]
    li a0, cLF
    li a7, 0	
    ecall
    li a7, 2
    la a0, in_msg
    ecall
    la a0, msg
    ecall
    li a0, cLF
    li a7, 0	
    ecall
    li a7, 2
    la a0, key_msg_real
    ecall
    li a7, 3
    lw a0, k
    mv a1, a0
    ecall
    la a0, msg
    jal encrypt
    li a0, cLF
    li a7, 0	
    ecall
    li a7, 2
    la a0, out_msg
    ecall
    la a0, msg
    ecall
    la a0, msg
    lw a1, k
    jal decrypt
    li a0, cLF
    li a7, 0	
    ecall
    li a7, 2
    la a0, orig_msg
    ecall
    la a0, msg
    ecall
    li a0, cLF
    li a7, 0	
    ecall
    la a0, end_msg
    li a7, 2
    ecall   
    li a7, 5
    ecall
