
const {useState} = wp.element
export default function useInstanceId() {
    const [id] = useState(() => Math.random().toString(36).substring(2, 10));
    return id;
}
